<?php

namespace App\Http\Controllers;

use App\Models\CameraLens;
use App\Models\ChatEvent;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    public function index()
    {
        return view('chat.index');
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $userMessage = $request->input('message');

        try {
            $userChatMessage = ChatMessage::create([
                'user_id' => Auth::id(),
                'session_id' => session()->getId(),
                'message' => $userMessage,
                'is_user' => true,
            ]);

            $relevantProducts = collect();
            $productContext = '';
            $systemPrompt = $this->buildSystemPrompt($productContext);
            $aiResponse = null;
            $nlpResult = null;

            if ($this->isGreetingMessage($userMessage)) {
                $aiResponse = $this->buildGreetingReply();
            } else {
                $relevantProducts = $this->findRelevantProducts($userMessage);
                $productContext = $this->buildProductContext($relevantProducts);
                $systemPrompt = $this->buildSystemPrompt($productContext);
                $aiResponse = $this->buildScriptedReply($userMessage, $relevantProducts);
            }

            if ($aiResponse === null && env('PY_CHAT_ENABLED', false) && filled(env('PY_CHAT_URL'))) {
                try {
                    $existingSession = ChatSession::where('session_id', session()->getId())->first();

                    $nlpResponse = Http::timeout(5)->post(rtrim(env('PY_CHAT_URL', ''), '/') . '/chat', [
                        'session_id' => session()->getId(),
                        'user_id' => Auth::id(),
                        'message' => $userMessage,
                        'product_context' => $productContext,
                        'state' => $existingSession?->state,
                        'last_intent' => $existingSession?->last_intent,
                        'metadata' => [
                            'user_agent' => $request->userAgent(),
                            'ip' => $request->ip(),
                        ],
                    ]);

                    if ($nlpResponse->successful()) {
                        $nlpResult = $nlpResponse->json();

                        foreach ($nlpResult['events'] ?? [] as $event) {
                            ChatEvent::create([
                                'chat_message_id' => $userChatMessage->id,
                                'user_id' => Auth::id(),
                                'session_id' => session()->getId(),
                                'type' => $event['type'] ?? 'nlp',
                                'payload' => $event['payload'] ?? [],
                            ]);
                        }

                        if (($nlpResult['handoff']['required'] ?? false) === true) {
                            $aiResponse = $nlpResult['handoff']['message'] ?? 'Mình sẽ chuyển bạn sang nhân viên để hỗ trợ kỹ hơn.';
                        } elseif (!empty($nlpResult['reply'])) {
                            $aiResponse = $nlpResult['reply'];
                        }

                        if (!empty($nlpResult['lead'])) {
                            Lead::updateOrCreate(
                                [
                                    'email' => $nlpResult['lead']['email'] ?? null,
                                    'phone' => $nlpResult['lead']['phone'] ?? null,
                                    'session_id' => session()->getId(),
                                ],
                                [
                                    'user_id' => Auth::id(),
                                    'name' => $nlpResult['lead']['name'] ?? null,
                                    'source' => 'chatbot',
                                    'status' => 'new',
                                    'metadata' => $nlpResult['lead']['metadata'] ?? [],
                                ]
                            );
                        }
                    }
                } catch (\Throwable $exception) {
                    // Dịch vụ NLP Python là tùy chọn; nếu không chạy, chatbot vẫn trả lời bằng kịch bản Laravel.
                }
            }

            if ($aiResponse === null && filled(env('OPENROUTER_API_KEY'))) {
                try {
                    $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . env('OPENROUTER_API_KEY'),
                        'Content-Type' => 'application/json',
                        'HTTP-Referer' => request()->getSchemeAndHttpHost(),
                        'X-Title' => 'MienTayShop Assistant',
                    ])->post('https://openrouter.ai/api/v1/chat/completions', [
                        'model' => env('OPENROUTER_MODEL', 'anthropic/claude-3.5-haiku'),
                        'messages' => [
                            ['role' => 'system', 'content' => $systemPrompt],
                            ['role' => 'user', 'content' => $userMessage],
                        ],
                        'max_tokens' => 1200,
                        'temperature' => 0.45,
                    ]);

                    if ($response->successful()) {
                        $data = $response->json();
                        $aiResponse = $data['choices'][0]['message']['content'] ?? null;
                    }
                } catch (\Throwable $exception) {
                    // API AI bên ngoài là tùy chọn; lỗi mạng không được làm gián đoạn chatbot bán hàng.
                }
            }

            if ($aiResponse === null) {
                $aiResponse = $this->buildCatalogFallbackReply($userMessage, $relevantProducts);
            }

            $aiChatMessage = ChatMessage::create([
                'user_id' => Auth::id(),
                'session_id' => session()->getId(),
                'message' => $aiResponse,
                'is_user' => false,
            ]);

            $chatSession = ChatSession::firstOrCreate(
                ['session_id' => session()->getId()],
                [
                    'user_id' => Auth::id(),
                    'total_messages' => 0,
                    'total_user_messages' => 0,
                    'total_ai_messages' => 0,
                ]
            );

            $chatSession->user_id = Auth::id();
            $chatSession->state = $nlpResult['state'] ?? $chatSession->state;
            $chatSession->last_intent = $nlpResult['intent']['name'] ?? $chatSession->last_intent;
            $chatSession->last_confidence = $nlpResult['intent']['confidence'] ?? $chatSession->last_confidence;
            $chatSession->last_message_at = now();
            $chatSession->handoff_requested = ($nlpResult['handoff']['required'] ?? false) === true;
            $chatSession->contact_collected = !empty($nlpResult['lead']);
            $chatSession->save();

            $chatSession->increment('total_messages', 2);
            $chatSession->increment('total_user_messages', 1);
            $chatSession->increment('total_ai_messages', 1);

            return response()->json([
                'success' => true,
                'message' => $aiResponse,
                'user_message_id' => $userChatMessage->id,
                'ai_message_id' => $aiChatMessage->id,
            ]);
        } catch (\Throwable $e) {
            $errorMessage = 'Xin lỗi, chatbot đang bận một chút. Bạn vui lòng thử lại sau ít phút nhé.';

            ChatMessage::create([
                'user_id' => Auth::id(),
                'session_id' => session()->getId(),
                'message' => $errorMessage,
                'is_user' => false,
            ]);

            return response()->json([
                'success' => false,
                'message' => $errorMessage,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getChatHistory(Request $request)
    {
        $messages = ChatMessage::where(function ($query) {
            if (Auth::id()) {
                $query->where('user_id', Auth::id());
                return;
            }

            $query->where('session_id', session()->getId());
        })->orderBy('created_at')->take(50)->get();

        return response()->json([
            'success' => true,
            'messages' => $messages,
        ]);
    }

    public function clearChat(Request $request)
    {
        ChatMessage::where(function ($query) {
            if (Auth::id()) {
                $query->where('user_id', Auth::id());
                return;
            }

            $query->where('session_id', session()->getId());
        })->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa lịch sử chat.',
        ]);
    }

    private function findRelevantProducts(string $message)
    {
        $normalized = $this->expandCatalogAliases($this->normalizeVietnamese($message));
        $keywords = $this->extractSearchKeywords($message);

        if ($keywords->isEmpty()) {
            return collect();
        }

        return CameraLens::active()
            ->with('categories')
            ->get()
            ->map(function (CameraLens $product) use ($normalized, $keywords) {
                return [
                    'product' => $product,
                    'score' => $this->scoreProductMatch($product, $normalized, $keywords),
                ];
            })
            ->filter(fn ($item) => $item['score'] > 0)
            ->sortByDesc('score')
            ->take(6)
            ->pluck('product')
            ->values();
    }

    private function extractSearchKeywords(string $message)
    {
        $normalized = $this->expandCatalogAliases($this->normalizeVietnamese($message));
        $keywords = collect();

        $brands = [
            'apple', 'iphone', 'samsung', 'xiaomi', 'oppo', 'realme', 'vivo', 'lenovo',
            'sony', 'jbl', 'anker', 'baseus', 'marshall', 'philips', 'vinamilk',
            'abbott', 'nestle', 'meiji', 'friso', 'nutifood', 'oatside', 'oreo',
            'pringles', 'oishi', 'poca', '3m', 'unicharm', 'dettol', 'lifebuoy',
            'huggies', 'pampers', 'merries', 'sunhouse', 'locknlock', 'elmich',
            'thien long', 'pilot', 'deli', 'telab', 'coolmate', 'routine', 'juno',
            'casio', 'asus', 'dell', 'canon', 'black rouge', 'anessa', 'pigeon',
            'bitis', 'first news', 'nha nam', 'lavie', 'acecook', 'kokomi',
        ];

        foreach ($brands as $brand) {
            if (str_contains($normalized, $brand)) {
                $keywords->push($brand);
            }
        }

        $productTerms = [
            'dien thoai', 'smartphone', 'iphone', 'iphone 15', 'iphone 14', 'iphone 13',
            'android', 'tablet', 'ipad', 'tai nghe', 'tws', 'headset', 'loa', 'speaker',
            'sac', 'cu sac', 'pin du phong', 'power bank', 'sua', 'sua bot', 'sua tuoi',
            'sua hat', 'ngu coc', 'banh', 'keo', 'socola', 'snack', 'mi', 'chao',
            'khau trang', 'sat khuan', 'rua tay', 'cham soc', 'ta', 'bim', 'em be',
            'noi', 'hop dung', 'binh nuoc', 'but', 'vo', 'giay', 'balo', 'van phong pham',
            'gia dung', 'ao polo', 'ao thun', 'thoi trang', 'giay dep', 'dam', 'vay',
            'laptop', 'may tinh', 'may anh', 'dong ho', 'sach', 'son', 'kem chong nang',
            'tui xach', 'bong da', 'vali', 'mu bao hiem', 'nuoc khoang',
        ];

        foreach ($productTerms as $term) {
            if (str_contains($normalized, $term)) {
                $keywords->push($term);
            }
        }

        if (preg_match('/(\d+)-(\d+)/', $normalized, $matches)) {
            $keywords->push($matches[1] . '-' . $matches[2]);
        }

        if (preg_match('/(\d+)(mm|mp|w|tb|gb|mah|ml|g|l)/', $normalized, $matches)) {
            $keywords->push($matches[1] . $matches[2]);
            $keywords->push($matches[1]);
        }

        $stopWords = [
            'toi', 'minh', 'ban', 'shop', 'can', 'muon', 'tim', 'kiem', 'mua',
            'xem', 'co', 'khong', 'cho', 'voi', 'nhe', 'san', 'pham', 'hang',
            'loai', 'nao', 'tot', 'dang', 'gia', 'duoi', 'tren', 'tu', 'den',
            'la', 'cua', 'the', 'hay', 'giup', 'goi', 'y', 've', 'hoi', 'xin',
            'chao', 'bao', 'nhieu', 'nhu', 'nao', 'cach', 'dat',
        ];

        foreach (preg_split('/[^\pL\pN]+/u', $normalized, -1, PREG_SPLIT_NO_EMPTY) as $token) {
            if (mb_strlen($token) >= 2 && !in_array($token, $stopWords, true)) {
                $keywords->push($token);
            }
        }

        return $keywords
            ->merge($this->keywordVariants($message))
            ->map(fn ($term) => $this->expandCatalogAliases($this->normalizeVietnamese((string) $term)))
            ->filter(fn ($term) => mb_strlen($term) >= 2 && !in_array($term, $stopWords, true))
            ->unique()
            ->values();
    }

    private function scoreProductMatch(CameraLens $product, string $normalizedMessage, $keywords): int
    {
        $name = $this->normalizeVietnamese($product->name ?? '');
        $brand = $this->normalizeVietnamese($product->brand ?? '');
        $type = $this->normalizeVietnamese($product->product_type ?? '');
        $search = $this->normalizeVietnamese($product->search_keywords ?? '');
        $description = $this->normalizeVietnamese($product->description ?? '');
        $categories = $this->normalizeVietnamese($product->categories->pluck('display_name')->implode(' '));
        $fullText = trim("{$name} {$brand} {$type} {$search} {$description} {$categories}");
        $score = 0;

        $phrases = collect([$normalizedMessage])
            ->merge($keywords->filter(fn ($keyword) => str_contains($keyword, ' ')))
            ->map(fn ($keyword) => trim((string) $keyword))
            ->filter(fn ($keyword) => mb_strlen($keyword) >= 3)
            ->unique();

        foreach ($phrases as $phrase) {
            if (str_contains($name, $phrase)) {
                $score += 120;
            } elseif (str_contains($fullText, $phrase)) {
                $score += 70;
            }
        }

        foreach ($keywords as $keyword) {
            $keyword = trim((string) $keyword);

            if (mb_strlen($keyword) < 2) {
                continue;
            }

            if (str_contains($name, $keyword)) {
                $score += 32;
            }

            if (str_contains($brand, $keyword)) {
                $score += 24;
            }

            if (str_contains($type, $keyword) || str_contains($categories, $keyword)) {
                $score += 18;
            }

            if (str_contains($search, $keyword)) {
                $score += 14;
            }

            if (str_contains($description, $keyword)) {
                $score += 4;
            }
        }

        if ($product->in_stock) {
            $score += 5;
        }

        return $score;
    }

    private function buildProductContext($products): string
    {
        if ($products->isEmpty()) {
            return '';
        }

        $cleanContext = "THÔNG TIN SẢN PHẨM ĐANG CÓ:\n";

        foreach ($products as $product) {
            $cleanContext .= "- {$product->name} ({$product->brand})\n";
            $cleanContext .= "  + Loại: {$product->display_product_type}\n";

            foreach ($product->display_specifications as $spec) {
                $cleanContext .= "  + {$spec['label']}: {$spec['value']}\n";
            }

            if ($product->categories->isNotEmpty()) {
                $cleanContext .= "  + Danh mục: {$product->categories->pluck('display_name')->implode(', ')}\n";
            }

            $cleanContext .= "  + Giá: {$product->formatted_price}\n";
            $cleanContext .= "  + Link: " . route('products.show', $product->id) . "\n";
            $cleanContext .= "  + Còn hàng: " . ($product->in_stock ? 'Có' : 'Hết hàng') . "\n\n";
        }

        return $cleanContext;
    }

    private function buildSystemPrompt(string $productContext): string
    {
        $cleanPrompt = "Bạn là trợ lý bán hàng của MienTayShop, một shop đa ngành giống sàn thương mại điện tử mini. "
            . "Luôn trả lời bằng tiếng Việt có dấu, ngắn gọn, dễ đọc và đúng câu hỏi. "
            . "Nếu khách chào thì chào lại thân thiện. Nếu khách hỏi sản phẩm, hãy ưu tiên gợi ý đúng sản phẩm trong catalog, kèm link markdown dạng [Tên sản phẩm](link). "
            . "Bạn có thể tư vấn điện thoại, tablet, tai nghe, loa, sạc, pin dự phòng, sữa, ngũ cốc, bánh kẹo, snack, khẩu trang, mẹ và bé, gia dụng và văn phòng phẩm.";

        if ($productContext !== '') {
            $cleanPrompt .= "\n\n" . $productContext;
            $cleanPrompt .= "Nếu không có sản phẩm khớp hoàn toàn, hãy đề xuất sản phẩm gần nhất và nói rõ vì sao phù hợp.";
        }

        return $cleanPrompt;
    }

    private function buildScriptedReply(string $message, $products): ?string
    {
        $normalized = $this->expandCatalogAliases($this->normalizeVietnamese($message));

        if (str_contains($normalized, 'troi hom nay') || str_contains($normalized, 'thoi tiet')) {
            return 'Mình là chatbot bán hàng nên chưa xem thời tiết trực tiếp được. Nhưng nếu bạn cần mua đồ theo thời tiết, mình có thể gợi ý áo khoác, khẩu trang, bình giữ nhiệt, kem chống nắng hoặc đồ đi mưa trong shop.';
        }

        if (str_contains($normalized, 'san pham dang hot') || str_contains($normalized, 'hang hot') || str_contains($normalized, 'hot')) {
            $hotProducts = CameraLens::active()->inStock()->orderByDesc('stock_quantity')->take(5)->get();

            return "Các sản phẩm đang hot ở MienTayShop:\n" . $this->formatProductLines($hotProducts)
                . "\nBạn có thể bấm vào tên sản phẩm để xem chi tiết hoặc bấm Mua ngay trên thẻ sản phẩm.";
        }

        if (str_contains($normalized, 'cach de dat hang') || str_contains($normalized, 'dat hang nhu nao') || str_contains($normalized, 'huong dan dat hang')) {
            return "Cách đặt hàng rất đơn giản:\n"
                . "1. Tìm sản phẩm bạn muốn mua.\n"
                . "2. Bấm “Thêm” để đưa vào giỏ hoặc “Mua ngay”.\n"
                . "3. Đăng nhập tài khoản.\n"
                . "4. Nhập thông tin nhận hàng và xác nhận đơn.\n"
                . "5. Đơn thành công sẽ nằm trong mục Lịch sử mua hàng.";
        }

        if (str_contains($normalized, 'thanh toan') || str_contains($normalized, 'tra tien') || str_contains($normalized, 'cod')) {
            return 'MienTayShop hỗ trợ thanh toán khi nhận hàng (COD) và chuyển khoản ngân hàng. Với chuyển khoản, bạn tạo đơn trước, chuyển đúng số tiền và nội dung hiển thị ở trang thanh toán để người bán xác nhận nhanh.';
        }

        if (str_contains($normalized, 'co tot khong') || str_contains($normalized, 'tot khong') || str_contains($normalized, 'danh gia')) {
            if ($products->isNotEmpty()) {
                $product = $products->first();
                $rating = round((float) $product->approvedReviews()->avg('rating'), 1);
                $count = $product->approvedReviews()->count();
                $ratingText = $count > 0 ? "Điểm đánh giá hiện tại: {$rating}/5 từ {$count} đánh giá." : 'Sản phẩm này chưa có nhiều đánh giá, bạn có thể xem thêm thông tin chi tiết trước khi quyết định mua.';

                return "{$product->name} của {$product->brand} là lựa chọn phù hợp nếu bạn đang tìm nhóm {$product->display_product_type}. {$ratingText}\n"
                    . "Bạn có thể xem chi tiết tại [" . $product->name . "](" . route('products.show', $product->id) . ").";
            }

            return 'Bạn muốn hỏi sản phẩm nào có tốt không? Hãy gửi tên sản phẩm hoặc thương hiệu, mình sẽ xem thông tin và đánh giá để tư vấn sát hơn.';
        }

        if ($products->isNotEmpty() && (str_contains($normalized, 'tim') || str_contains($normalized, 'san pham') || str_contains($normalized, 'goi y'))) {
            return "Mình tìm thấy các sản phẩm phù hợp:\n" . $this->formatProductLines($products)
                . "\nBạn muốn mình lọc tiếp theo giá, thương hiệu hay danh mục nào không?";
        }

        return null;
    }

    private function formatProductLines($products): string
    {
        return $products->take(5)->map(function ($product) {
            return "- [{$product->name}](" . route('products.show', $product->id) . ") - {$product->brand}, {$product->formatted_price}";
        })->implode("\n");
    }

    private function buildCatalogFallbackReply(string $message, $products): string
    {
        if ($this->isGreetingMessage($message)) {
            return $this->buildGreetingReply();
        }

        $normalized = $this->expandCatalogAliases($this->normalizeVietnamese($message));

        return $this->buildCleanCatalogFallbackReply($normalized, $products);
    }

    private function buildCleanCatalogFallbackReply(string $normalized, $products): string
    {
        $lines = [];

        if (str_contains($normalized, 'giao hang') || str_contains($normalized, 'ship')) {
            $lines[] = 'Shop hỗ trợ giao hàng toàn quốc. Sau khi bạn đặt hàng, đơn sẽ được lưu vào lịch sử mua hàng để shop xác nhận và xử lý.';
        } elseif (str_contains($normalized, 'thanh toan') || str_contains($normalized, 'cod') || str_contains($normalized, 'chuyen khoan')) {
            $lines[] = 'Shop hỗ trợ thanh toán khi nhận hàng (COD) và chuyển khoản ngân hàng.';
        } elseif (str_contains($normalized, 'bao hanh')) {
            $lines[] = 'Tùy nhóm hàng, shop sẽ có chính sách bảo hành hoặc hỗ trợ đổi lỗi phù hợp. Với điện thoại/phụ kiện, bạn nên giữ hóa đơn để đối chiếu.';
        } elseif (str_contains($normalized, 'doi tra') || str_contains($normalized, 'hoan tien')) {
            $lines[] = 'Nếu sản phẩm lỗi hoặc không đúng mô tả, bạn có thể liên hệ shop để được hỗ trợ đổi trả.';
        } else {
            $lines[] = 'Mình đã lọc nhanh một vài sản phẩm phù hợp trong catalog để bạn tham khảo.';
        }

        if ($products->isEmpty()) {
            $lines[] = 'Hiện mình chưa thấy sản phẩm nào khớp thật sát. Bạn thử nói rõ hơn tên sản phẩm, thương hiệu hoặc mức giá mong muốn nhé.';

            return implode("\n", $lines);
        }

        $lines[] = 'Gợi ý nhanh cho bạn:';

        foreach ($products->take(4) as $product) {
            $lines[] = "- [{$product->name}](" . route('products.show', $product->id) . ") - {$product->display_product_type}, {$product->formatted_price}, " . ($product->in_stock ? 'còn hàng' : 'tạm hết hàng');
        }

        if (str_contains($normalized, 'so sanh') || str_contains($normalized, 'nen chon')) {
            $lines[] = 'Bạn có thể nói thêm ngân sách hoặc mục đích sử dụng để mình so sánh và chọn sản phẩm hợp nhất.';
        } elseif (str_contains($normalized, 'mua') || str_contains($normalized, 'dat hang')) {
            $lines[] = 'Bạn có thể bấm “Thêm” hoặc “Mua ngay” trên sản phẩm để đặt hàng. Đơn đặt thành công sẽ hiện trong Lịch sử mua hàng.';
        } else {
            $lines[] = 'Nếu muốn lọc sát hơn, bạn hãy nói thêm ngân sách, thương hiệu hoặc nhu cầu sử dụng nhé.';
        }

        return implode("\n", $lines);
    }

    private function isGreetingMessage(string $message): bool
    {
        $normalized = $this->normalizeVietnamese($message);
        $normalized = preg_replace('/[^\pL\pN\s]+/u', ' ', $normalized) ?: $normalized;
        $normalized = trim(preg_replace('/\s+/', ' ', $normalized) ?: $normalized);

        $greetings = [
            'xin chao',
            'chao',
            'hello',
            'hi',
            'alo',
            'shop oi',
            'bot oi',
        ];

        return in_array($normalized, $greetings, true)
            || preg_match('/^(xin chao|chao|hello|hi|alo)( shop| ban| bot)?$/u', $normalized) === 1;
    }

    private function buildGreetingReply(): string
    {
        return "Xin chào! Mình là chatbot tư vấn của MienTayShop. Bạn muốn tìm sản phẩm nào hôm nay?\n"
            . "Mình có thể gợi ý điện thoại iPhone/Samsung, sữa cho bé, tai nghe, loa, bánh kẹo, khẩu trang, mẹ và bé, gia dụng hoặc văn phòng phẩm. "
            . "Bạn chỉ cần gửi tên sản phẩm, thương hiệu, mức giá hoặc nhu cầu sử dụng, mình sẽ lọc sản phẩm phù hợp cho bạn.";
    }

    private function expandCatalogAliases(string $value): string
    {
        $aliases = [
            'ip 14 pro' => 'iphone 14 pro',
            'ip14 pro' => 'iphone 14 pro',
            'ip 15 plus' => 'iphone 15 plus',
            'ip15 plus' => 'iphone 15 plus',
            'ip 15' => 'iphone 15',
            'ip15' => 'iphone 15',
            'iphone15' => 'iphone 15',
            'ip 14' => 'iphone 14',
            'ip14' => 'iphone 14',
            'iphone14' => 'iphone 14',
            'dt' => 'dien thoai',
            'dien thoại' => 'dien thoai',
            'tai nghe bluetooth' => 'tai nghe tws',
            'headphone' => 'tai nghe headset',
            'mask' => 'khau trang',
            'khẩu trang' => 'khau trang',
            'sữa' => 'sua',
            'bánh' => 'banh',
        ];

        foreach ($aliases as $from => $to) {
            $value = str_replace($from, $to, $value);
        }

        return $value;
    }

    private function normalizeVietnamese(string $value): string
    {
        return Str::of($value)->ascii()->lower()->squish()->value();
    }

    private function keywordVariants(string $value): array
    {
        $normalized = $this->expandCatalogAliases($this->normalizeVietnamese($value));

        return collect([$value, $normalized])
            ->filter()
            ->flatMap(function ($term) {
                $parts = preg_split('/[^\pL\pN]+/u', $term, -1, PREG_SPLIT_NO_EMPTY) ?: [];

                return array_merge([$term], $parts);
            })
            ->map(fn ($term) => trim((string) $term))
            ->filter(fn ($term) => mb_strlen($term) >= 2)
            ->unique()
            ->values()
            ->all();
    }
}
