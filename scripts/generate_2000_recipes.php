<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';

echo "Bắt đầu tạo bộ 2000 công thức nấu ăn phong phú, chuẩn mực ẩm thực...\n";

$db = db();

// Check current max ID
$maxId = (int)$db->query("SELECT COALESCE(MAX(id), 0) FROM recipes")->fetchColumn();
echo "ID hiện tại lớn nhất: $maxId\n";

// Target: Add ~2000 recipes (from $maxId + 1 up to $maxId + 2000)
$authorIds = [1, 2, 4, 5, 6, 7, 8, 9];

// Curated image pools by domain
$imgMap = [
    'hau' => 'assets/images/real_dishes/oc-huong-xao-bo-toi.jpg',
    'tom' => 'assets/images/real_dishes/tom-su-hap-nuoc-dua.jpg',
    'tom_chien' => 'assets/images/real_dishes/tom-chien-hoang-bao.jpg',
    'tom_xao' => 'assets/images/real_dishes/tom-xao-bong-cai.jpg',
    'muc' => 'assets/images/real_dishes/muc-ong-hap-gung-sa.jpg',
    'muc_chien' => 'assets/images/real_dishes/muc-chien-nuoc-mam.jpg',
    'ca' => 'assets/images/real_dishes/ca-loc-kho-to.jpg',
    'ca_hap' => 'assets/images/real_dishes/ca-dieu-hong-hap-xi-dau.jpg',
    'ca_chien' => 'assets/images/real_dishes/ca-tai-tuong-chien-xu.jpg',
    'bo' => 'assets/images/real_dishes/bo-xao-hanh-tay.jpg',
    'bo_steak' => 'assets/images/real_dishes/bo-ne-hoa-thien-ly.jpg',
    'bo_kho' => 'assets/images/real_dishes/bo-kho-gung-sa.jpg',
    'heo_kho' => 'assets/images/real_dishes/thit-kho-tieu-xanh.jpg',
    'heo_suon' => 'assets/images/real_dishes/suon-ram-man-ngot.jpg',
    'heo_dua' => 'assets/images/real_dishes/thit-kho-cui-dua.jpg',
    'ga_kho' => 'assets/images/real_dishes/ga-kho-gung.jpg',
    'ga_hap' => 'assets/images/real_dishes/ga-hap-muoi-hot.jpg',
    'ga_chien' => 'assets/images/real_dishes/canh-ga-chien-nuoc-mam.jpg',
    'canh_suon' => 'assets/images/real_dishes/canh-suon-cu-sen.jpg',
    'canh_chua' => 'assets/images/real_dishes/canh-chua-tom-dua.jpg',
    'canh_trung' => 'assets/images/real_dishes/canh-ca-chua-trung.jpg',
    'canh_ngao' => 'assets/images/real_dishes/canh-ngao-nau-chua.jpg',
    'canh_chay' => 'assets/images/real_dishes/canh-nam-hat-sen-chay.jpg',
    'chay' => 'assets/images/real_dishes/dau-hu-non-sot-nam.jpg',
    'chay_chien' => 'assets/images/real_dishes/dau-phu-chien-gion.jpg',
    'chay_bun' => 'assets/images/real_dishes/bun-xao-chay.jpg',
    'banh' => 'assets/images/real_dishes/nem-chay-ran-gion.jpg',
    'banh_cha' => 'assets/images/real_dishes/cha-gio-re-hai-san.jpg',
    'banh_xoi' => 'assets/images/real_dishes/xoi-vo-hat-sen.jpg',
    'pho' => 'assets/images/real_dishes/canh-bong-thap-cam.jpg',
    'mi' => 'assets/images/real_dishes/com-chien-dua-bo.jpg',
    'au_my' => 'assets/images/real_dishes/trung-cuon-xuc-xich.jpg',
];

$recipesToInsert = [];
$targetCount = 2000;
$currentId = $maxId + 1;

// Helper to assemble clean ingredients text
function format_ing(array $items): string {
    return implode("\n", $items);
}

// Helper to assemble clean steps
function format_steps(array $steps): string {
    $out = [];
    foreach ($steps as $idx => $s) {
        $stepNum = $idx + 1;
        $out[] = "Bước {$stepNum}: " . trim($s);
    }
    return implode("\n\n", $out);
}

echo "Đang khởi tạo các nhóm công thức chi tiết...\n";

// MODULE 1: Hàu, Tôm & Hải Sản Đa Dạng (~220 món)
$seafoodDishes = [
    // Hàu
    [
        'title' => 'Hàu nướng phô mai béo ngậy',
        'desc' => 'Hàu sữa tươi béo múp đút lò cùng sốt phô mai Mozzarella kéo sợi, bơ tỏi thơm lừng hấp dẫn.',
        'cat' => 'Món nướng',
        'time' => '20 phút',
        'servings' => '3 người',
        'ing' => [
            '10 con Hàu sữa tươi sống tách vỏ',
            '100g Phô mai Mozzarella bào sợi',
            '2 thìa canh Sốt mayonnaise',
            '20g Bơ lạt đun chảy',
            '1 thìa canh Sữa tươi không đường',
            '1 thìa cà phê Tiêu xay',
            'Hành lá, rau răm ăn kèm'
        ],
        'steps' => [
            'Dùng bàn chải chà sạch vỏ hàu dưới vòi nước, tách nhẹ nửa vỏ trên rồi để ráo.',
            'Trộn đều sốt gồm mayonnaise, bơ lạt đun chảy, sữa tươi và chút tiêu xay.',
            'Phết đều hỗn hợp sốt lên từng con hàu, rắc phô mai Mozzarella ngập mặt.',
            'Nướng ở nồi chiên không dầu hoặc lò nướng ở 180°C trong 8 - 10 phút đến khi phô mai vàng xém thơm phức.'
        ],
        'tips' => 'Nên chọn hàu sữa tươi sống ruột mẩy, không nướng quá lâu kẻo hàu bị teo khô mất nước ngọt tự nhiên.',
        'cal' => 380, 'p' => 24, 'c' => 12, 'f' => 26, 'tag' => 'Hải sản, Giàu kẽm, Món tiệc', 'img' => $imgMap['hau']
    ],
    [
        'title' => 'Hàu nướng mỡ hành đậu phộng giòn',
        'desc' => 'Món hàu nướng mỡ hành đậm đà dân dã, hàu ngọt nước quyện cùng mỡ hành thơm nức và đậu phộng rang bùi bùi.',
        'cat' => 'Món nướng',
        'time' => '15 phút',
        'servings' => '4 người',
        'ing' => [
            '12 con Hàu sữa tươi làm sạch',
            '50g Hành lá thái nhỏ',
            '3 thìa canh Dầu ăn',
            '30g Đậu phộng rang giã dập',
            '1 thìa cà phê Nước mắm ngon',
            '1/2 thìa cà phê Hạt nêm',
            '1 quả Ớt sừng băm nhỏ'
        ],
        'steps' => [
            'Hàu cọ sạch vỏ, để ráo nước trên khay.',
            'Đun nóng dầu ăn, trút hành lá, chút nước mắm, hạt nêm và ớt băm vào đảo nhanh 10 giây rồi tắt bếp để hành giữ màu xanh.',
            'Rưới mỡ hành lên mặt từng con hàu.',
            'Nướng than hoa hoặc nồi chiên không dầu 190°C trong 6 - 8 phút. Rắc đậu phộng rang giòn trước khi thưởng thức cùng muối tiêu chanh.'
        ],
        'tips' => 'Rưới mỡ hành vừa phải lúc nướng, không đun dầu quá sôi làm hành bị úa vàng.',
        'cal' => 320, 'p' => 22, 'c' => 10, 'f' => 21, 'tag' => 'Hải sản, Món nhậu, Truyền thống', 'img' => $imgMap['hau']
    ],
    [
        'title' => 'Cháo hàu hạt sen tẩm bổ',
        'desc' => 'Tô cháo hàu nóng hổi, hạt sen bùi bở, hàu ngọt đậm đà bổ dưỡng giúp phục hồi sức khỏe tuyệt vời.',
        'cat' => 'Món canh',
        'time' => '40 phút',
        'servings' => '4 người',
        'ing' => [
            '300g Ruột hàu sữa tươi',
            '100g Gạo tẻ thơm',
            '30g Gạo nếp dẻo',
            '50g Hạt sen tươi thông tâm',
            '2 củ Hành tím băm nhỏ',
            '1 nhánh Gừng thái chỉ',
            'Hành hoa, rau răm, tiêu xay'
        ],
        'steps' => [
            'Vo sạch gạo tẻ và gạo nếp, ninh nhừ cùng hạt sen và 1.2 lít nước trên lửa nhỏ.',
            'Ruột hàu rửa nhẹ tay với nước muối loãng pha chút chanh để sạch nhớt, để ráo.',
            'Phi thơm hành tím với chút dầu ăn, cho hàu vào xào nhanh lửa lớn trong 2 phút cùng thìa nước mắm, hạt nêm.',
            'Khi cháo nhừ sánh mịn, trút hàu đã xào vào nồi khuấy đều, đun sôi lại 3 phút rồi tắt bếp. Múc ra tô rắc gừng sợi, hành răm và tiêu.'
        ],
        'tips' => 'Không ninh hàu chung với gạo từ đầu vì hàu sẽ teo nhỏ và mất vị ngọt tươi; xào chín tới rồi cho vào sau.',
        'cal' => 350, 'p' => 26, 'c' => 45, 'f' => 6, 'tag' => 'Dinh dưỡng, Món canh, Bồi bổ', 'img' => $imgMap['canh_chua']
    ],
    [
        'title' => 'Canh hàu nấu chua dứa cà chua',
        'desc' => 'Vị chua thanh mát lành của dứa chín và cà chua quyện cùng vị ngọt béo tự nhiên của ruột hàu sữa.',
        'cat' => 'Món canh',
        'time' => '25 phút',
        'servings' => '4 người',
        'ing' => [
            '250g Ruột hàu sữa tươi',
            '1/4 quả Dứa chín thái lát',
            '2 quả Cà chua bổ múi cau',
            '2 quả Me hoặc sấu chua',
            '2 củ Hành tím băm',
            'Hành lá, thì là, rau ngổ',
            'Nước mắm, hạt nêm, tiêu xay'
        ],
        'steps' => [
            'Hàu rửa sạch nhẹ tay, ướp chút hạt nêm và hành tím băm.',
            'Phi thơm hành tím, xào cà chua lấy màu rồi cho dứa và me chua vào đảo đều.',
            'Chế 800ml nước lọc đun sôi, dầm me lấy vị chua thanh dịu.',
            'Thả hàu vào nồi nước canh sôi sùng sục, nêm nước mắm vừa ăn, đun sôi lại 2 phút. Cho hành lá, thì là, rau ngổ rồi tắt bếp.'
        ],
        'tips' => 'Hàu chín rất nhanh, chỉ cần sôi bùng lên là tắt bếp để thịt hàu mọng nước không bị dai.',
        'cal' => 210, 'p' => 19, 'c' => 15, 'f' => 5, 'tag' => 'Món canh, Thanh nhiệt, Ít calo', 'img' => $imgMap['canh_chua']
    ],
    [
        'title' => 'Hàu né bơ tỏi ăn kèm bánh mì',
        'desc' => 'Thịt hàu béo múp ngập trong sốt bơ tỏi vàng óng, thơm nức mũi chấm cùng bánh mì giòn tan nóng hổi.',
        'cat' => 'Món xào',
        'time' => '20 phút',
        'servings' => '2 người',
        'ing' => [
            '300g Ruột hàu sữa',
            '30g Bơ lạt',
            '1 củ Tỏi băm nhỏ',
            '1 quả Trứng gà',
            '1 thìa canh Dầu hào',
            '1 thìa cà phê Đường phèn',
            '1/2 thìa cà phê Tiêu xay',
            'Bánh mì nóng giòn ăn kèm'
        ],
        'steps' => [
            'Ruột hàu sơ chế sạch để thật ráo nước.',
            'Làm nóng chảo gang, cho bơ lạt vào đun chảy rồi phi thơm tỏi băm ngả vàng nhạt.',
            'Cho hàu vào xào lửa lớn trong 2 phút, nêm dầu hào, chút đường và tiêu xay.',
            'Đập quả trứng gà vào chảo cạnh hàu, đun thêm 1 phút cho lòng đỏ lòng đào rồi nhấc chảo ra thưởng thức ngay.'
        ],
        'tips' => 'Dùng chảo gang giữ nhiệt lâu giúp sốt bơ sôi liu riu và hàu giữ trọn độ mọng béo.',
        'cal' => 460, 'p' => 28, 'c' => 22, 'f' => 29, 'tag' => 'Món xào, Bổ dưỡng, Món ngon mỗi ngày', 'img' => $imgMap['hau']
    ],
    [
        'title' => 'Hàu chiên trứng gà vàng ươm',
        'desc' => 'Món trứng đúc hàu mềm béo, thơm lừng vị hành hoa, ăn cùng cơm nóng rất đưa cơm cho cả gia đình.',
        'cat' => 'Món chiên',
        'time' => '15 phút',
        'servings' => '3 người',
        'ing' => [
            '200g Ruột hàu tươi',
            '3 quả Trứng gà ta',
            '3 nhánh Hành lá thái nhỏ',
            '1 củ Hành tím băm',
            '1 thìa canh Nước mắm',
            '1/2 thìa cà phê Tiêu xay',
            '2 thìa canh Dầu ăn'
        ],
        'steps' => [
            'Ruột hàu rửa sạch, chần sơ qua nước sôi có chút gừng trong 20 giây rồi vớt ra để ráo.',
            'Đập trứng gà vào bát, đánh tan cùng nước mắm, tiêu xay và hành lá.',
            'Phi thơm hành tím trên chảo dầu, cho hàu vào đảo nhanh 1 phút rồi dàn đều ra mặt chảo.',
            'Đổ đều trứng gà ngập hàu, hạ lửa vừa đậy vung 3 phút cho trứng chín phồng vàng hai mặt.'
        ],
        'tips' => 'Chần sơ hàu trước giúp khi chiên trứng không bị ra nước làm mềm nát trứng.',
        'cal' => 310, 'p' => 21, 'c' => 4, 'f' => 23, 'tag' => 'Món chiên, Tiết kiệm thời gian, Giàu đạm', 'img' => $imgMap['canh_trung']
    ],

    // Tôm
    [
        'title' => 'Tôm sú hấp nước dừa tươi ngọt đậm',
        'desc' => 'Tôm sú tươi sống hấp chín bằng nước dừa xiêm, thịt tôm đỏ au săn chắc, ngọt thanh tự nhiên.',
        'cat' => 'Món hấp',
        'time' => '15 phút',
        'servings' => '4 người',
        'ing' => [
            '500g Tôm sú tươi sống',
            '1 quả Dừa xiêm lấy nước',
            '2 nhánh Sả đập dập',
            '1 củ Gừng thái lát',
            '1/2 thìa cà phê Muối biển'
        ],
        'steps' => [
            'Tôm sú cắt bớt râu và ngạnh nhọn, rửa sạch để ráo.',
            'Đổ nước dừa tươi vào nồi, thả sả đập dập và gừng lát, đun sôi bùng.',
            'Thả tôm sú vào nồi nước dừa, đậy nắp hấp lửa lớn trong 5 - 7 phút đến khi tôm cong đều chuyển màu đỏ au.',
            'Vớt tôm ra đĩa, chấm cùng muối tiêu chanh ớt hoặc muối ớt xanh Nha Trang.'
        ],
        'tips' => 'Không hấp quá 8 phút làm tôm mất vị ngọt và thịt bị khô xác.',
        'cal' => 210, 'p' => 38, 'c' => 5, 'f' => 3, 'tag' => 'Hải sản, Eat Clean, Không dầu mỡ', 'img' => $imgMap['tom']
    ],
    [
        'title' => 'Tôm rim mặn ngọt đậm đà đưa cơm',
        'desc' => 'Món tôm rim truyền thống vỏ ngoài bóng bẩy ngấm sốt mặn ngọt cay cay, cắn vào giòn sần sật.',
        'cat' => 'Món kho',
        'time' => '25 phút',
        'servings' => '3 người',
        'ing' => [
            '400g Tôm tươi cắt râu',
            '2 thìa canh Nước mắm ngon',
            '1.5 thìa canh Đường hoa mai',
            '1 củ Tỏi băm nhuyễn',
            '2 củ Hành tím băm',
            '1 thìa cà phê Tiêu đen xay',
            '1 nhánh Hành hoa cắt khúc'
        ],
        'steps' => [
            'Tôm rửa sạch để ráo, ướp với chút hạt nêm trong 10 phút.',
            'Cho đường vào chảo cùng chút dầu ăn đun lửa nhỏ đến khi chuyển màu cánh gián đẹp mắt.',
            'Phi thơm hành tỏi băm, trút tôm vào đảo đều tay trên lửa lớn đến khi vỏ tôm săn lại và lên màu đỏ cam.',
            'Nêm nước mắm, hạ lửa vừa rim liu riu cho nước sốt sánh kẹo bám chặt quanh thân tôm. Rắc hành hoa và tiêu xay rồi tắt bếp.'
        ],
        'tips' => 'Đảo tôm lửa lớn lúc đầu giúp vỏ tôm giòn bóng và thịt tôm săn chắc.',
        'cal' => 280, 'p' => 34, 'c' => 14, 'f' => 8, 'tag' => 'Món kho, Đậm vị, Bữa cơm gia đình', 'img' => $imgMap['tom_chien']
    ],
    [
        'title' => 'Tôm xào bông cải xanh sốt dầu hào',
        'desc' => 'Món xào thanh đạm kết hợp tôm giòn ngọt và bông cải xanh tươi mát, giàu vitamin và chất xơ.',
        'cat' => 'Món xào',
        'time' => '15 phút',
        'servings' => '3 người',
        'ing' => [
            '300g Tôm nõn bóc vỏ chừa đuôi',
            '1 cây Bông cải xanh thái miếng vừa ăn',
            '1 củ Cà rốt tỉa hoa thái lát',
            '3 tép Tỏi đập dập băm nhỏ',
            '1.5 thìa canh Dầu hào',
            '1 thìa canh Dầu ăn',
            'Hạt nêm, tiêu xay'
        ],
        'steps' => [
            'Chần sơ bông cải xanh và cà rốt qua nước sôi có chút muối trong 1 phút rồi vớt ra ngâm nước đá lạnh để giữ màu xanh giòn.',
            'Phi thơm tỏi băm trên chảo dầu, cho tôm nõn vào xào nhanh 2 phút đến khi tôm vừa chín tới chuyển màu hồng thì trút ra đĩa riêng.',
            'Cho bông cải và cà rốt vào chảo đảo đều lửa lớn, nêm dầu hào, hạt nêm.',
            'Trút tôm lại chảo đảo đều trong 1 phút cho ngấm sốt rồi rắc tiêu xay và tắt bếp.'
        ],
        'tips' => 'Ngâm rau vào nước đá sau khi chần giúp rau giữ được độ giòn sần sật và màu sắc tươi tắn.',
        'cal' => 220, 'p' => 29, 'c' => 12, 'f' => 6, 'tag' => 'Món xào, Eat Clean, Giàu xơ', 'img' => $imgMap['tom_xao']
    ],
    [
        'title' => 'Tôm chiên hoàng bào giòn rụm',
        'desc' => 'Từng con tôm tươi cuộn trong lớp khoai tây vàng ươm giòn tan, chấm cùng sốt mayonnaise béo thơm.',
        'cat' => 'Món chiên',
        'time' => '30 phút',
        'servings' => '4 người',
        'ing' => [
            '400g Tôm sú to bóc vỏ chừa đuôi',
            '2 củ Khoai tây gọt vỏ',
            '50g Bột chiên giòn',
            '1 thìa cà phê Hạt nêm',
            'Dầu ăn để chiên ngập dầu'
        ],
        'steps' => [
            'Tôm ướp nhẹ với chút hạt nêm và tiêu.',
            'Khoai tây dùng dao bào thành sợi dài mỏng, ngâm nước muối loãng 10 phút cho dẻo rồi vớt ra thấm khô.',
            'Lăn tôm qua lớp bột chiên giòn mỏng, quấn sợi khoai tây quanh thân tôm đều đặn.',
            'Thả tôm vào chảo dầu nóng chiên ngập dầu trên lửa vừa đến khi khoai tây vàng ươm giòn rụm thì vớt ra giấy thấm dầu.'
        ],
        'tips' => 'Ngâm khoai tây nước muối giúp sợi khoai dẻo dai dễ quấn quanh tôm mà không bị gãy vụn.',
        'cal' => 390, 'p' => 31, 'c' => 28, 'f' => 18, 'tag' => 'Món chiên, Món tiệc, Khai vị', 'img' => $imgMap['tom_chien']
    ]
];

// Combine variations for seafood to make 180 distinct dishes
$seafoodProteins = [
    ['Tôm sú', 'Món tôm sú', 'tom'],
    ['Tôm thẻ', 'Món tôm thẻ tươi', 'tom'],
    ['Tôm càng xanh', 'Món tôm càng xanh', 'tom'],
    ['Hàu sữa', 'Món hàu sữa', 'hau'],
    ['Hàu đại dương', 'Món hàu đại dương', 'hau'],
    ['Mực ống', 'Món mực ống tươi', 'muc'],
    ['Mực lá', 'Món mực lá dày mình', 'muc'],
    ['Bạch tuộc', 'Món bạch tuộc giòn sần sật', 'muc'],
    ['Nghêu bến tre', 'Món nghêu tươi', 'canh_ngao'],
    ['Ngao hai cùi', 'Món ngao hai cùi ngọt lịm', 'canh_ngao'],
    ['Sò huyết', 'Món sò huyết đầm ô loan', 'hau'],
    ['Cua biển Cà Mau', 'Món cua biển thịt chắc nịch', 'tom'],
    ['Ghẹ xanh', 'Món ghẹ xanh hấp', 'tom'],
    ['Ốc hương', 'Món ốc hương', 'hau'],
    ['Ốc móng tay', 'Món ốc móng tay', 'hau'],
];

$seafoodCookingStyles = [
    ['nướng phô mai bỏ lò', 'Món nướng', 25, '3 người', 'hau', 360, 26, 10, 24, 'Nướng đút lò cùng phô mai béo ngậy'],
    ['nướng mỡ hành đậu phộng', 'Món nướng', 20, '4 người', 'hau', 310, 25, 8, 20, 'Nướng mỡ hành thơm phức, rắc lạc rang giòn'],
    ['nướng muối ớt cay nồng', 'Món nướng', 20, '3 người', 'tom', 240, 30, 4, 8, 'Tẩm ướp muối ớt cay xé lưỡi, vỏ giòn thịt ngọt'],
    ['hấp nước dừa sả ớt', 'Món hấp', 15, '4 người', 'tom', 210, 34, 6, 4, 'Hấp nước dừa ngọt dịu thoảng hương sả ấm nồng'],
    ['hấp gừng hành thơm nức', 'Món hấp', 15, '4 người', 'muc', 190, 32, 4, 3, 'Hấp cách thủy cùng gừng tươi và hành hoa'],
    ['xào bơ tỏi thơm lừng', 'Món xào', 15, '3 người', 'tom_xao', 320, 28, 8, 20, 'Xào lửa lớn cùng bơ thơm và tỏi băm giòn'],
    ['xào sả ớt đậm đà', 'Món xào', 18, '3 người', 'tom_xao', 260, 27, 7, 12, 'Xào sả ớt giòn thơm, cay the the đưa cơm'],
    ['chiên giòn sốt me chua ngọt', 'Món chiên', 25, '4 người', 'tom_chien', 340, 26, 24, 15, 'Chiên giòn rụm rồi phủ sốt me sánh quyện'],
    ['chiên bơ tỏi giòn tan', 'Món chiên', 20, '3 người', 'muc_chien', 350, 28, 14, 19, 'Chiên giòn ngập dầu rồi xóc bơ tỏi thơm phức'],
    ['nấu canh chua dứa thì là', 'Món canh', 25, '4 người', 'canh_chua', 220, 24, 12, 6, 'Nấu canh chua thanh mát cùng dứa chín, cà chua và thì là'],
    ['nấu cháo đậu xanh hạt sen', 'Món canh', 45, '4 người', 'canh_chua', 320, 26, 42, 5, 'Ninh cháo nhừ sánh cùng đậu xanh hạt sen bồi bổ'],
    ['kho tiêu xanh cay nồng', 'Món kho', 30, '3 người', 'heo_kho', 270, 32, 6, 11, 'Kho liu riu với tiêu sọ xanh đậm đà'],
];

foreach ($seafoodProteins as $p) {
    foreach ($seafoodCookingStyles as $s) {
        $title = $p[0] . ' ' . $s[0];
        $cat = $s[1];
        $time = $s[2] . ' phút';
        $servings = $s[3];
        $imgKey = $s[4];
        $cal = $s[5];
        $prot = $s[6];
        $carb = $s[7];
        $fat = $s[8];

        $ingList = [
            "450g {$p[0]} tươi sống",
            "3 tép Tỏi đập dập băm nhỏ",
            "2 củ Hành tím thái mỏng",
            "1 thìa canh Dầu hào",
            "1 thìa cà phê Hạt nêm",
            "1 thìa cà phê Tiêu xay",
            "Hành lá, rau thơm ăn kèm"
        ];

        $steps = [
            "Sơ chế làm sạch {$p[0]} kỹ lưỡng dưới vòi nước, để ráo hoàn toàn.",
            "Ướp cùng tỏi băm, hạt nêm và chút tiêu trong 10 phút cho ngấm đều gia vị.",
            "Tiến hành {$s[9]} trên ngọn lửa chuẩn để giữ trọn độ mọng nước và ngọt thịt tự nhiên.",
            "Hoàn thiện món ăn, trình bày ra đĩa cùng rau thơm và nước chấm phù hợp."
        ];

        $recipesToInsert[] = [
            'title' => $title,
            'description' => "Công thức chế biến {$p[1]} {$s[0]} chuẩn vị, hải sản tươi giòn ngọt tự nhiên.",
            'category' => $cat,
            'cooking_time' => $time,
            'servings' => $servings,
            'ingredients' => format_ing($ingList),
            'instructions' => format_steps($steps),
            'tips' => "Bí quyết chọn {$p[0]} tươi sống và chế biến vừa chín tới để thịt không bị teo khô dai.",
            'calories' => $cal,
            'protein' => $prot,
            'carbs' => $carb,
            'fat' => $fat,
            'dietary_tags' => 'Hải sản, Giàu đạm, Dinh dưỡng',
            'image_url' => $imgMap[$imgKey] ?? $imgMap['hau']
        ];
    }
}
echo "Đã sinh " . count($recipesToInsert) . " món hải sản & hàu tôm.\n";

// MODULE 2: Thịt Heo & Thịt Lợn (~350 món)
$porkCuts = [
    'Thịt ba chỉ heo', 'Thịt ba chỉ lợn', 'Sườn heo non', 'Sườn lợn non',
    'Thịt nạc vai heo', 'Thịt nạc vai lợn', 'Chân giò heo', 'Chân giò lợn',
    'Thịt thăn heo', 'Thịt thăn lợn', 'Thịt heo xay', 'Thịt lợn xay',
    'Tai heo', 'Tai lợn', 'Ba rọi heo giòn bì', 'Thịt dọi lợn'
];

$porkStyles = [
    ['kho tàu nước dừa trứng cút', 'Món kho', 45, '4 người', 'heo_kho', 450, 28, 12, 34, 'Kho nước dừa liu riu cho miếng thịt mềm rục béo ngậy'],
    ['kho tiêu xanh đậm đà', 'Món kho', 30, '3 người', 'heo_kho', 380, 30, 6, 26, 'Kho kẹo với tiêu sọ cay ấm thơm nồng'],
    ['kho cùi dừa giòn sần sật', 'Món kho', 35, '4 người', 'heo_dua', 420, 27, 9, 31, 'Kho cùng cùi dừa rám giòn ngọt béo'],
    ['kho măng tươi giòn ngon', 'Món kho', 40, '4 người', 'heo_kho', 360, 29, 10, 23, 'Kho cùng măng tươi khử vị chua đưa cơm'],
    ['xào sả ớt cay thơm', 'Món xào', 20, '3 người', 'bo', 340, 31, 6, 22, 'Xào lửa lớn với sả ớt thái mỏng thơm phức'],
    ['xào chua ngọt bóng bẩy', 'Món xào', 25, '3 người', 'heo_suon', 390, 26, 22, 21, 'Xóc sốt chua ngọt từ giấm gạo và cà chua'],
    ['xào hành tây cần tây', 'Món xào', 15, '3 người', 'bo', 310, 29, 8, 18, 'Xào chín tới cùng hành tây ngọt giòn'],
    ['chiên xù giòn tan kiểu Tonkatsu', 'Món chiên', 25, '3 người', 'ca_chien', 430, 32, 24, 23, 'Tẩm bột chiên xù vàng rụm giòn tan'],
    ['nướng riềng mẻ thơm nức', 'Món nướng', 35, '4 người', 'heo_suon', 410, 33, 8, 28, 'Ướp riềng xay, mẻ ngấu và mắm tôm nướng than hoa'],
    ['nướng mật ong vừng thơm', 'Món nướng', 30, '4 người', 'heo_suon', 440, 31, 16, 29, 'Quết mật ong rừng nướng vàng óng ả'],
    ['hấp gừng hành thanh ngọt', 'Món hấp', 25, '3 người', 'ca_hap', 290, 34, 4, 16, 'Hấp cách thủy giữ trọn vị ngọt tự nhiên'],
    ['nấu canh sườn chua sấu', 'Món canh', 35, '4 người', 'canh_suon', 280, 26, 12, 14, 'Nấu canh sấu thanh mát giải nhiệt mùa hè'],
    ['nấu canh bắp cải cuộn thịt', 'Món canh', 30, '4 người', 'canh_chua', 260, 25, 11, 13, 'Bọc thịt băm mộc nhĩ trong lá bắp cải ngọt lịm'],
    ['nấu canh bí đỏ hầm sườn', 'Món canh', 40, '4 người', 'canh_suon', 320, 24, 22, 15, 'Hầm cùng bí đỏ dẻo bùi bồi dưỡng khí huyết'],
];

foreach ($porkCuts as $cut) {
    foreach ($porkStyles as $ps) {
        $title = $cut . ' ' . $ps[0];
        $recipesToInsert[] = [
            'title' => $title,
            'description' => "Cách làm {$cut} {$ps[0]} chuẩn vị truyền thống thơm ngon, thịt mềm ngọt ngấm gia vị.",
            'category' => $ps[1],
            'cooking_time' => $ps[2] . ' phút',
            'servings' => $ps[3],
            'ingredients' => format_ing([
                "450g {$cut} tươi mới mổ",
                "2 củ Hành tím băm nhỏ",
                "3 tép Tỏi đập dập",
                "1.5 thìa canh Nước mắm cốt",
                "1 thìa cà phê Hạt nêm",
                "1/2 thìa cà phê Tiêu xay",
                "Gia vị gia đình vừa đủ"
            ]),
            'instructions' => format_steps([
                "Sơ chế {$cut} rửa sạch với nước muối loãng, trần sơ qua nước sôi rồi để ráo.",
                "Ướp thịt cùng hành tím, tỏi băm, nước mắm và tiêu trong 15 phút.",
                "Thực hiện {$ps[8]} trên ngọn lửa vừa cho gia vị ngấm sâu vào từng thớ thịt.",
                "Bày ra đĩa, ăn cùng cơm trắng nóng hổi rất bắt cơm."
            ]),
            'tips' => "Mẹo chọn {$cut} tươi có màu hồng tự nhiên, thớ thịt săn chắc không có mùi lạ.",
            'calories' => $ps[5],
            'protein' => $ps[6],
            'carbs' => $ps[7],
            'fat' => $ps[8],
            'dietary_tags' => 'Thịt heo, Thịt lợn, Bữa cơm gia đình, Giàu đạm',
            'image_url' => $imgMap[$ps[4]] ?? $imgMap['heo_kho']
        ];
    }
}
echo "Đã sinh tổng cộng " . count($recipesToInsert) . " công thức sau nhóm Thịt Heo / Lợn.\n";

// MODULE 3: Bún, Phở, Mì, Miến (~380 món)
$noodleBases = [
    ['Phở bò', 'Món bún & phở', 'pho', 'Bánh phở tươi mềm mướt quyện nước dùng ninh xương bò trong veo thơm nức hồi quế.'],
    ['Phở gà', 'Món bún & phở', 'pho', 'Phở gà ta da vàng giòn, thịt chắc ngọt, nước dùng thanh trong thơm mùi lá chanh.'],
    ['Phở xào', 'Món bún & phở', 'pho', 'Phở xào áp chảo cháy cạnh thơm lừng, thịt mềm rau cải ngọt giòn.'],
    ['Phở cuốn', 'Món bún & phở', 'pho', 'Bánh phở cuốn thịt bò xào thơm nức, rau thơm chấm mắm chua ngọt.'],
    ['Bún bò', 'Món bún & phở', 'pho', 'Bún bò đậm đà chuẩn vị xứ Huế cay nồng thơm mùi mắm ruốc và sả.'],
    ['Bún chả', 'Món bún & phở', 'pho', 'Chả miếng chả băm nướng than hoa thơm lừng ăn kèm bún tươi và nước chấm đu đủ.'],
    ['Bún riêu', 'Món bún & phở', 'pho', 'Nước dùng riêu cua đồng thơm ngọt thanh tao quyện giấm bỗng và cà chua đỏ au.'],
    ['Bún tôm', 'Món bún & phở', 'pho', 'Tô bún tôm tươi ngọt nước, thanh mát đậm đà hương vị biển cả.'],
    ['Bún cá', 'Món bún & phở', 'pho', 'Cá chiên giòn rụm bên ngoài ngọt mềm bên trong cùng nước dùng chua thanh.'],
    ['Bún mọc', 'Món bún & phở', 'pho', 'Mọc giòn dai sần sật nấm hương cùng sườn non ninh mềm rục.'],
    ['Bún thang', 'Món bún & phở', 'pho', 'Món bún thang Hà Nội thanh tao cầu kỳ với giò lụa, trứng tráng mỏng, thịt gà xé.'],
    ['Mì Quảng', 'Món bún & phở', 'mi', 'Sợi mì Quảng vàng óng dẻo dai ăn cùng tôm thịt đậm đà, bánh tráng nướng giòn rụm.'],
    ['Mì vịt tiềm', 'Món bún & phở', 'mi', 'Đùi vịt tiềm thảo mộc da giòn thịt mềm ngấm vị thuốc bắc bổ dưỡng.'],
    ['Mì xào giòn', 'Món bún & phở', 'mi', 'Mì chiên phồng giòn tan rưới đẫm sốt hải sản và rau củ sền sệt.'],
    ['Mì vằn thắn', 'Món bún & phở', 'mi', 'Mì sợi dai trứng thơm ngát, há cảo nhân tôm thịt đậm đà xá xíu thơm mềm.'],
    ['Mì Ý Spaghetti', 'Món Âu - Mỹ', 'au_my', 'Mì Ý luộc chuẩn al dente đẫm sốt bò băm thơm lừng phô mai Parmesan.'],
    ['Mì Ý Carbonara', 'Món Âu - Mỹ', 'au_my', 'Sợi pasta quyện kem tươi béo ngậy, thịt xông khói áp chảo giòn thơm nức mũi.'],
    ['Mì Ramen', 'Món bún & phở', 'mi', 'Nước dùng ninh xương đậm đà béo ngậy, thịt xá xíu mềm tan cùng trứng lòng đào.'],
    ['Mì Udon', 'Món bún & phở', 'mi', 'Sợi mì Udon to tròn dẻo dai xào hải sản hoặc nấu nước súp Dashi thanh ngọt.'],
    ['Miến gà', 'Món bún & phở', 'canh_suon', 'Miến dong dai giòn nấu nước dùng gà ta trong vắt, lòng mề nấm hương.'],
    ['Miến xào cua', 'Món bún & phở', 'tom_xao', 'Sợi miến tơi mềm ngấm trọn vị ngọt đậm từ thịt cua biển xào lửa lớn.'],
    ['Miến lươn giòn', 'Món bún & phở', 'ca_chien', 'Lươn đồng chiên giòn tan ăn cùng miến dong xào hoặc chan nước dùng ngọt thanh.'],
    ['Miến trộn Hàn Quốc', 'Món bún & phở', 'chay_bun', 'Miến Japchae trộn dầu mè thơm phức, thịt bò và đủ loại rau củ thanh đạm.'],
    ['Miến măng vịt', 'Món bún & phở', 'canh_suon', 'Nước dùng măng chua ngọt thanh, thịt vịt chấm nước mắm gừng cay nồng.']
];

$noodleToppings = [
    ['tái chín đậm đà', '40 phút', '2 người', 460, 32, 62, 10],
    ['nạm gầu giòn sần sật', '50 phút', '3 người', 510, 34, 60, 16],
    ['sốt vang thơm mùi rượu vang', '60 phút', '4 người', 540, 33, 64, 18],
    ['xào bò mềm rau cải xanh', '20 phút', '2 người', 480, 31, 58, 14],
    ['trộn sốt chua ngọt cay the', '20 phút', '2 người', 410, 26, 60, 9],
    ['hải sản tôm mực chua cay', '30 phút', '3 người', 430, 35, 56, 8],
    ['thịt nướng than hoa', '35 phút', '3 người', 520, 32, 62, 15],
    ['sườn non nấm hương', '45 phút', '4 người', 470, 30, 58, 13],
    ['thập cẩm tôm thịt chả giò', '35 phút', '3 người', 530, 33, 65, 14],
    ['xá xíu trứng lòng đào', '30 phút', '2 người', 490, 31, 59, 15],
    ['bò băm sốt cà chua phô mai', '25 phút', '2 người', 560, 30, 68, 18],
    ['sốt kem nấm thịt xông khói', '25 phút', '2 người', 590, 27, 65, 25],
    ['lòng mề mộc nhĩ nấm hương', '30 phút', '3 người', 420, 29, 54, 10],
    ['thanh đạm rau nấm chay', '20 phút', '2 người', 340, 16, 58, 6],
    ['măng khô sườn non', '45 phút', '4 người', 450, 28, 56, 12],
    ['gà xé lá chanh thơm nức', '35 phút', '3 người', 430, 34, 55, 8],
];

foreach ($noodleBases as $nb) {
    foreach ($noodleToppings as $nt) {
        $title = $nb[0] . ' ' . $nt[0];
        $recipesToInsert[] = [
            'title' => $title,
            'description' => "{$nb[3]} Biến tấu hấp dẫn với topping {$nt[0]} thơm ngon khó cưỡng.",
            'category' => $nb[1],
            'cooking_time' => $nt[1],
            'servings' => $nt[2],
            'ingredients' => format_ing([
                "350g Sợi {$nb[0]} tươi hoặc khô",
                "250g Topping chính ({$nt[0]})",
                "1 lít Nước hầm xương ngọt thanh",
                "1 củ Hành tây hoặc hành hoa",
                "1 nhánh Gừng tươi đập dập",
                "2 thìa canh Nước mắm ngon",
                "Rau thơm ăn kèm, chanh, ớt tươi"
            ]),
            'instructions' => format_steps([
                "Trần sợi bún/phở/mì qua nước sôi cho tơi mềm rồi xóc ráo nước, chia ra từng tô.",
                "Chế biến phần topping {$nt[0]} xào chín tới hoặc luộc thái lát mỏng vừa ăn.",
                "Nấu sôi nước dùng thơm ngát, nêm nếm gia vị đậm đà vừa miệng.",
                "Xếp thịt và rau thơm lên mặt tô mì/phở, chan nước dùng sôi sùng sục rồi thưởng thức nóng."
            ]),
            'tips' => "Nước dùng phải luôn giữ thật sôi khi chan vào bát để thịt và bánh phở ngấm trọn hương vị nóng hổi.",
            'calories' => $nt[3],
            'protein' => $nt[4],
            'carbs' => $nt[5],
            'fat' => $nt[6],
            'dietary_tags' => 'Món bún phở mì, Truyền thống, Bữa sáng ấm bụng',
            'image_url' => $imgMap[$nb[2]] ?? $imgMap['pho']
        ];
    }
}
echo "Đã sinh tổng cộng " . count($recipesToInsert) . " công thức sau nhóm Bún Phở Mì Miến.\n";

// MODULE 4: Các Loại Bánh Truyền Thống, Bánh Ngọt, Tráng Miệng (~380 món)
$cakeBases = [
    ['Bánh xèo', 'Món chiên', 'banh_cha', 'Vỏ bánh xèo vàng ươm mỏng giòn rụm, cuộn cùng tôm thịt giá đỗ và rau sống tươi mát.'],
    ['Bánh khọt', 'Món chiên', 'banh_cha', 'Từng chiếc bánh khọt nhỏ xinh giòn tan mặt đáy, nhân tôm tươi đỏ au rắc mỡ hành.'],
    ['Bánh cuốn', 'Món hấp', 'banh', 'Bánh cuốn tráng mỏng mềm mướt, cuộn thịt băm mộc nhĩ thơm nức chấm mắm cà cuống.'],
    ['Bánh bao', 'Món hấp', 'banh', 'Vỏ bánh bao trắng xốp nở bung mềm mịn, nhân thịt trứng cút đậm đà béo ngậy.'],
    ['Bánh giò', 'Món hấp', 'banh', 'Bánh giò gói lá chuối mềm mượt tan trên đầu lưỡi, nhân thịt băm mộc nhĩ thơm nồng.'],
    ['Bánh bèo', 'Món hấp', 'banh', 'Bánh bèo chén dẻo mềm rắc tôm cháy đỏ hồng, mỡ hành óng ả và tóp mỡ giòn rụm.'],
    ['Bánh bột lọc', 'Món hấp', 'banh', 'Vỏ bánh trong suốt dai dai nhìn rõ nhân tôm thịt kho rim đậm đà.'],
    ['Bánh nậm', 'Món hấp', 'banh', 'Bánh nậm gói lá dong dẹt mỏng, thơm bùi bột gạo và tôm thịt băm nhuyễn.'],
    ['Bánh chưng', 'Món luộc', 'banh_xoi', 'Bánh chưng xanh rền thơm mùi lá dong, nếp cái hoa vàng dẻo quánh quyện đỗ xanh thịt mỡ.'],
    ['Bánh tét', 'Món luộc', 'banh_xoi', 'Đòn bánh tét tròn trịa ngày Tết, nếp dẻo bùi béo ngậy vị đậu xanh và thịt mỡ tiêu cay.'],
    ['Bánh mì Việt Nam', 'Món bánh', 'banh', 'Vỏ bánh mì giòn tan rôm rốp, ruột mềm kẹp pate gan, chả lụa, thịt xá xíu và đồ chua.'],
    ['Bánh bông lan', 'Món bánh', 'banh', 'Cốt bánh bông lan mềm xốp ẩm mịn thơm nức hương bơ sữa và trứng gà.'],
    ['Bánh flan', 'Món bánh', 'banh', 'Bánh flan caramel mềm mịn mượt mà không rỗ, ngọt thanh mát lạnh béo ngậy.'],
    ['Bánh tart trứng', 'Món bánh', 'banh', 'Vỏ bánh ngàn lớp giòn rụm nhiều tầng, nhân kem trứng sữa nướng vàng óng.'],
    ['Bánh muffin', 'Món bánh', 'banh', 'Bánh nướng phồng xốp thơm lừng mùi hoa quả và socola chip ngọt ngào.'],
    ['Bánh brownie', 'Món bánh', 'banh', 'Bánh brownie socola đậm đà đắng nhẹ ngọt thơm, ruột ẩm mềm đặc quánh hấp dẫn.'],
    ['Bánh chuối nướng', 'Món bánh', 'banh', 'Chuối chín thơm lừng nướng cùng nước cốt dừa và bánh mì dẻo quánh béo ngậy.'],
    ['Bánh da lợn', 'Món bánh', 'banh', 'Từng lớp bánh xanh lá dứa xen kẽ vàng đậu xanh dẻo dai thơm ngát cốt dừa.'],
    ['Pizza hải sản', 'Món Âu - Mỹ', 'au_my', 'Đế bánh pizza nướng giòn rụm ngập tràn tôm mực tươi ngon và phô mai kéo sợi.'],
    ['Pizza bò băm', 'Món Âu - Mỹ', 'au_my', 'Pizza sốt cà chua bò băm thảo mộc phủ phô mai Mozzarella đút lò vàng xém.'],
    ['Bánh rán lúc lắc', 'Món chiên', 'banh_xoi', 'Vỏ bánh nếp dẻo thơm rắc vừng rang, nhân đậu xanh ngọt bùi lúc lắc bên trong.']
];

$cakeFlavors = [
    ['tôm thịt mầm giá đỗ', '30 phút', '3 người', 380, 18, 45, 14],
    ['xá xíu phô mai kéo sợi', '35 phút', '3 người', 420, 22, 48, 16],
    ['trứng muối chà bông béo ngậy', '45 phút', '4 người', 440, 19, 52, 18],
    ['caramel cà phê thơm mát', '35 phút', '4 người', 260, 8, 38, 9],
    ['việt quất yến mạch ít ngọt', '30 phút', '4 người', 290, 9, 44, 9],
    ['socola hạnh nhân đậm đà', '35 phút', '4 người', 410, 10, 48, 20],
    ['nước cốt dừa lá dứa thơm lừng', '40 phút', '4 người', 330, 6, 54, 11],
    ['nhân thập cẩm gà quay', '60 phút', '4 người', 490, 24, 55, 19],
    ['phô mai Mozzarella ngập tràn', '25 phút', '3 người', 520, 26, 56, 22],
    ['đậu xanh cốt dừa hạt sen', '40 phút', '4 người', 320, 11, 56, 8],
    ['thịt băm nấm mèo mộc nhĩ', '25 phút', '3 người', 350, 17, 42, 13],
    ['bơ tỏi phô mai đút lò', '20 phút', '3 người', 460, 16, 50, 21],
    ['chuối chín mật ong rừng', '35 phút', '4 người', 280, 5, 52, 6],
    ['trà xanh matcha Nhật Bản', '35 phút', '4 người', 310, 9, 46, 10],
    ['dâu tây phô mai kem Mascarpone', '30 phút', '4 người', 370, 8, 42, 19],
    ['gà cay nấm phô mai', '30 phút', '3 người', 470, 25, 48, 20],
    ['thịt nướng sốt BBQ', '25 phút', '3 người', 480, 24, 50, 21],
    ['chay thanh đạm nấm rau củ', '25 phút', '3 người', 260, 10, 44, 5],
];

foreach ($cakeBases as $cb) {
    foreach ($cakeFlavors as $cf) {
        $title = $cb[0] . ' ' . $cf[0];
        $recipesToInsert[] = [
            'title' => $title,
            'description' => "{$cb[3]} Hương vị {$cf[0]} thơm ngon độc đáo được yêu thích nhất.",
            'category' => $cb[1],
            'cooking_time' => $cf[1],
            'servings' => $cf[2],
            'ingredients' => format_ing([
                "300g Bột hoặc nguyên liệu làm {$cb[0]}",
                "150g Nhân bánh ({$cf[0]})",
                "2 quả Trứng gà tươi",
                "50ml Sữa tươi hoặc nước cốt dừa",
                "30g Bơ lạt hoặc dầu ăn",
                "Đường phèn, muối, vani hoặc men nở vừa đủ"
            ]),
            'instructions' => format_steps([
                "Trộn đều bột cùng nước cốt và trứng gà, nhào kỹ hoặc khuấy mịn không vón cục.",
                "Chuẩn bị nhân bánh {$cf[0]} tẩm ướp thơm ngon vừa vặn.",
                "Tạo hình bánh khéo léo hoặc đổ khuôn theo kích thước mong muốn.",
                "Nướng hoặc hấp/chiên đúng nhiệt độ chuẩn đến khi vỏ ngoài vàng ruộm, ruột bánh chín mềm thơm nức."
            ]),
            'tips' => "Kiểm tra nhiệt độ lò hoặc lửa đều đặn giúp vỏ bánh vàng xốp đẹp mắt mà không bị khét.",
            'calories' => $cf[3],
            'protein' => $cf[4],
            'carbs' => $cf[5],
            'fat' => $cf[6],
            'dietary_tags' => 'Món bánh, Tráng miệng, Ăn vặt, Món ngon gia đình',
            'image_url' => $imgMap[$cb[2]] ?? $imgMap['banh']
        ];
    }
}
echo "Đã sinh tổng cộng " . count($recipesToInsert) . " công thức sau nhóm Món Bánh.\n";

// MODULE 5: Món Ăn Âu - Mỹ Chuẩn Nhà Hàng (~360 món)
$westernBases = [
    ['Bò Beefsteak', 'au_my', 'Miếng thịt bò thăn mềm mọng nước, áp chảo lửa lớn thơm bơ tỏi và lá hương thảo.'],
    ['Sườn heo BBQ', 'heo_suon', 'Sườn heo tảng ướp sốt BBQ đậm đà đút lò nướng chậm, thịt mềm róc xương thơm nức.'],
    ['Cá hồi áp chảo', 'au_my', 'Miếng cá hồi Na Uy tươi béo, lớp da giòn rụm bên trong mọng nước rưới sốt bơ chanh thơm lừng.'],
    ['Gà nướng thảo mộc', 'ga_chien', 'Gà nướng vàng ruộm óng ả ướp hương thảo rosemary, xạ hương thyme và bơ tỏi kiểu Pháp.'],
    ['Sườn cừu đút lò', 'au_my', 'Sườn cừu hảo hạng áp chảo nướng đút lò chín tới, không hề có mùi gây, thơm ngát thảo mộc.'],
    ['Súp kem bí đỏ', 'canh_chua', 'Súp bí đỏ sánh mịn vàng óng quyện kem sữa béo ngậy kiểu Pháp ăn cùng bánh mì bơ tỏi.'],
    ['Súp kem nấm', 'canh_chua', 'Nấm tươi thái lát nấu kem béo ngậy, ngọt dịu ấm bụng ngày se lạnh.'],
    ['Súp hành tây kiểu Pháp', 'canh_suon', 'Hành tây caramen hóa ngọt đậm đà ninh nước dùng bò, phủ bánh mì và phô mai nướng xém.'],
    ['Salad Caesar', 'chay', 'Rau xà lách Romaine giòn ngọt trộn sốt Caesar đặc sánh, phô mai Parmesan và bánh mì nướng giòn.'],
    ['Lasagna bò phô mai', 'au_my', 'Từng lớp pasta xếp xen kẽ sốt bò băm Bolognese, sốt kem Béchamel và phô mai đút lò nướng xém.'],
    ['Mac and Cheese', 'au_my', 'Mì nui ống ngập tràn trong sốt phô mai Cheddar béo ngậy thơm nức.'],
    ['Burger bò New York', 'au_my', 'Thịt bò xay nướng than hoa mềm ngọt kẹp phô mai chảy, rau xà lách, cà chua và sốt đặc biệt.'],
    ['Bò sốt vang kiểu Pháp', 'bo_kho', 'Thịt nạm bò ninh nhừ cùng rượu vang đỏ, cà rốt và khoai tây thơm ngào ngạt ăn kèm bánh mì.'],
    ['Bò Wellington', 'bo_steak', 'Thăn bò ngoại bọc nấm băm pate gan ngỗng và lớp bột ngàn lớp nướng vàng ruộm đẳng cấp.'],
    ['Xúc xích Đức nướng', 'au_my', 'Xúc xích Đức giòn dai đậm đà nướng than ăn kèm dưa cải chua Sauerkraut và mù tạt vàng.'],
];

$westernSauces = [
    ['sốt tiêu đen thơm nồng', '25 phút', '2 người', 490, 36, 12, 32],
    ['sốt bơ chanh caper béo dịu', '20 phút', '2 người', 460, 34, 8, 30],
    ['sốt phô mai kem nấm béo ngậy', '25 phút', '2 người', 540, 31, 14, 38],
    ['sốt rượu vang đỏ thượng hạng', '30 phút', '2 người', 520, 35, 15, 33],
    ['sốt BBQ Texas đậm đà', '35 phút', '3 người', 510, 33, 24, 30],
    ['sốt bơ tỏi hương thảo rosemary', '20 phút', '2 người', 480, 35, 6, 34],
    ['sốt mù tạt mật ong Dijon', '25 phút', '3 người', 470, 32, 18, 28],
    ['sốt kem nấm truffle quý phái', '30 phút', '2 người', 580, 30, 16, 42],
    ['sốt cà chua thảo mộc Oregano', '25 phút', '3 người', 390, 28, 22, 18],
    ['sốt phô mai Cheddar kéo sợi', '20 phút', '2 người', 560, 26, 32, 38],
    ['đút lò phô mai Mozzarella xém vàng', '35 phút', '4 người', 570, 32, 36, 31],
    ['ăn kèm khoai tây nghiền bơ tỏi', '30 phút', '2 người', 510, 33, 38, 24],
    ['ăn kèm măng tây áp chảo giòn ngọt', '20 phút', '2 người', 430, 36, 10, 26],
    ['nướng thảo mộc bơ tỏi kiểu Ý', '30 phút', '3 người', 490, 34, 8, 33],
    ['hầm rượu vang và rau củ kiểu Pháp', '50 phút', '4 người', 530, 35, 26, 28],
    ['áp chảo sốt cam mật ong', '20 phút', '2 người', 450, 33, 20, 24],
    ['nướng chậm giòn bì kiểu Âu', '45 phút', '4 người', 550, 34, 12, 39],
    ['sốt cay nồng kiểu Mexico', '25 phút', '3 người', 480, 32, 18, 30],
    ['sốt kem bơ tỏi măng tây', '25 phút', '2 người', 470, 35, 12, 31],
    ['đút lò bánh phồng kiểu Anh', '40 phút', '3 người', 540, 33, 28, 32],
    ['chiên xù sốt tartar béo ngậy', '25 phút', '3 người', 510, 31, 26, 31],
    ['trộn sốt giấm đen Balsamic', '15 phút', '2 người', 310, 18, 14, 19],
    ['súp kem ngô ngọt bơ béo', '25 phút', '3 người', 360, 12, 34, 20],
    ['nướng phô mai đút lò Parmesan', '30 phút', '3 người', 530, 32, 22, 34],
];

foreach ($westernBases as $wb) {
    foreach ($westernSauces as $ws) {
        $title = $wb[0] . ' ' . $ws[0];
        $recipesToInsert[] = [
            'title' => $title,
            'description' => "{$wb[2]} Kết hợp hoàn hảo cùng {$ws[0]} chuẩn phong cách ẩm thực Âu - Mỹ.",
            'category' => 'Món Âu - Mỹ',
            'cooking_time' => $ws[1],
            'servings' => $ws[2],
            'ingredients' => format_ing([
                "400g Nguyên liệu chính ({$wb[0]})",
                "30g Bơ lạt động vật",
                "2 tép Tỏi đập dập nguyên vỏ",
                "1 nhánh Hương thảo tươi (Rosemary)",
                "1 thìa cà phê Muối biển Kosher",
                "1 thìa cà phê Tiêu đen xay thô",
                "Sốt đặc trưng kèm rau củ áp chảo"
            ]),
            'instructions' => format_steps([
                "Thấm thật khô bề mặt thịt/hải sản, rắc muối tiêu đều hai mặt trước khi chế biến.",
                "Làm nóng chảo gang trên lửa lớn, cho dầu ăn và áp chảo vàng xém lớp vỏ ngoài.",
                "Thả bơ lạt, tỏi và nhánh hương thảo vào chảo, nghiêng chảo múc bơ rưới liên tục lên mặt thịt.",
                "Để thịt nghỉ 5 - 7 phút trước khi thái lát, rưới đều phần sốt thơm ngậy lên trên đĩa."
            ]),
            'tips' => "Thời gian để thịt nghỉ (Resting) cực kỳ quan trọng giúp nước ngọt thẩm thấu ngược lại thớ thịt mềm mọng.",
            'calories' => $ws[3],
            'protein' => $ws[4],
            'carbs' => $ws[5],
            'fat' => $ws[6],
            'dietary_tags' => 'Món Âu Mỹ, Sang trọng, Giàu đạm, Đậm chất nhà hàng',
            'image_url' => $imgMap[$wb[1]] ?? $imgMap['bo_steak']
        ];
    }
}
echo "Đã sinh tổng cộng " . count($recipesToInsert) . " công thức sau nhóm Món Âu - Mỹ.\n";

// MODULE 6: Món Chay Thanh Đạm & Eat Clean Khỏe Đẹp (~350 món)
$veganBases = [
    ['Đậu hũ non', 'chay', 'Đậu hũ non mềm tan như lụa, thanh mát giải độc cơ thể.'],
    ['Đậu phụ chiên', 'chay_chien', 'Đậu phụ vàng giòn bên ngoài, bên trong béo ngậy thơm mùi đậu nành.'],
    ['Nấm đùi gà', 'chay', 'Nấm đùi gà giòn ngọt sần sật, giàu đạm thực vật và khoáng chất.'],
    ['Nấm đông cô', 'chay', 'Nấm đông cô ngọt đậm đà, mùi thơm đặc trưng thanh lọc tâm hồn.'],
    ['Nấm kim châm', 'chay', 'Nấm kim châm giòn mát ngấm sốt đậm đà dễ ăn.'],
    ['Rau củ quả', 'chay', 'Rau củ tươi xanh theo mùa giòn ngọt tự nhiên bổ sung dồi dào chất xơ.'],
    ['Hạt sen tươi', 'canh_chay', 'Hạt sen bùi bùi thanh mát an thần ngủ ngon.'],
    ['Chả lụa chay', 'chay', 'Chả lụa làm từ đậu nành và nấm dai giòn thơm ngon đậm đà.'],
    ['Nem nấm chay', 'banh', 'Nem chay cuốn mộc nhĩ nấm hương rán giòn rụm chấm mắm chay chua ngọt.'],
    ['Canh chua nấm', 'canh_chay', 'Canh chua thanh ngọt dịu từ dứa chín, cà chua và các loại nấm tươi.'],
    ['Cơm chiên chay', 'mi', 'Hạt cơm tơi giòn xào cùng hạt sen, đậu Hà Lan và nấm hương thơm lừng.'],
    ['Miến xào chay', 'chay_bun', 'Sợi miến dong tơi dai xào cùng mộc nhĩ, nấm rơm và rau củ thanh ngọt.'],
    ['Lẩu nấm chay', 'canh_chay', 'Nước dùng ninh từ củ quả ngọt thanh tự nhiên, nhúng các loại nấm tươi mát.'],
    ['Cà tím om', 'chay', 'Cà tím mềm ngọt om cùng đậu phụ và lá lốt tía tô thơm ngát.'],
    ['Bí đỏ hầm', 'canh_chay', 'Bí đỏ ngọt bùi hầm đậu phộng và nấm rơm béo ngậy ấm bụng.'],
    ['Salad bơ dưa leo', 'chay', 'Salad bơ sáp béo ngậy và dưa leo giòn mát trộn sốt chanh leo chua ngọt.']
];

$veganStyles = [
    ['sốt cà chua hành hoa', 'Món xào', 15, '3 người', 180, 12, 14, 8],
    ['kho tiêu xanh đậm vị', 'Món kho', 25, '3 người', 190, 14, 16, 7],
    ['sốt dầu hào chay nấm hương', 'Món xào', 15, '3 người', 210, 15, 18, 9],
    ['kho sả ớt cay nồng', 'Món kho', 20, '3 người', 195, 13, 15, 8],
    ['chiên sả ớt giòn rụm', 'Món chiên', 20, '3 người', 240, 16, 12, 14],
    ['kho tương bần truyền thống', 'Món kho', 30, '4 người', 220, 15, 19, 9],
    ['om lá lốt tía tô thơm nức', 'Món xào', 20, '3 người', 170, 11, 14, 8],
    ['nấu canh chua thanh mát', 'Món canh', 25, '4 người', 150, 9, 18, 4],
    ['hầm hạt sen táo đỏ an thần', 'Món canh', 35, '4 người', 230, 12, 36, 5],
    ['kho quẹt chấm rau củ luộc', 'Món kho', 25, '4 người', 210, 14, 20, 8],
    ['xào hạt điều giòn bùi', 'Món xào', 20, '3 người', 280, 15, 22, 16],
    ['nấu súp kem ngô bí đỏ', 'Món canh', 30, '4 người', 190, 8, 30, 6],
    ['chiên xù sốt chua ngọt', 'Món chiên', 25, '3 người', 270, 13, 28, 12],
    ['cuốn gỏi rau mầm thanh lọc', 'Món hấp', 15, '3 người', 160, 10, 24, 3],
    ['nướng giấy bạc sốt sa tế', 'Món nướng', 25, '3 người', 220, 14, 16, 11],
    ['rim nước cốt dừa béo ngậy', 'Món kho', 25, '3 người', 260, 12, 22, 15],
    ['trộn sốt mè rang béo thơm', 'Món chay', 15, '2 người', 190, 9, 14, 12],
    ['xào miến dong thập cẩm', 'Món chay', 20, '3 người', 290, 11, 46, 7],
    ['hấp xì dầu hành hoa', 'Món hấp', 15, '3 người', 160, 13, 10, 6],
    ['lẩu nhúng thảo mộc thanh đạm', 'Món lẩu', 35, '4 người', 230, 14, 28, 6],
    ['kho củ cải mặn ngọt', 'Món kho', 25, '3 người', 180, 11, 18, 7],
    ['chiên giòn lắc phô mai chay', 'Món chiên', 20, '3 người', 250, 12, 24, 12]
];

foreach ($veganBases as $vb) {
    foreach ($veganStyles as $vs) {
        $title = $vb[0] . ' ' . $vs[0];
        $recipesToInsert[] = [
            'title' => $title,
            'description' => "{$vb[2]} Món ăn thanh tịnh, thuần chay {$vs[0]} bồi bổ sức khỏe cho cả gia đình.",
            'category' => $vs[1],
            'cooking_time' => $vs[2] . ' phút',
            'servings' => $vs[3],
            'ingredients' => format_ing([
                "350g {$vb[0]} tươi sạch",
                "2 nhánh Hành boa-rô (tỏi tây)",
                "1 thìa canh Nước tương ngon",
                "1 thìa canh Dầu hào chay",
                "1 thìa cà phê Hạt nêm nấm chay",
                "1 thìa cà phê Đường phèn",
                "Dầu mè, tiêu xay, ngò rí"
            ]),
            'instructions' => format_steps([
                "Rửa sạch các nguyên liệu thực vật, cắt miếng vừa ăn rồi để ráo.",
                "Phi thơm hành boa-rô với chút dầu mè cho dậy mùi thơm thanh dịu.",
                "Thực hiện nấu {$vs[0]} trên lửa vừa, nêm hạt nêm nấm và nước tương cho vừa khẩu vị.",
                "Múc món chay ra đĩa, rắc tiêu xay và ngò rí trang trí đẹp mắt."
            ]),
            'tips' => "Dùng hành boa-rô và dầu mè thay hành tỏi thông thường giúp món chay giữ trọn vị thanh tao và dậy mùi thơm tự nhiên.",
            'calories' => $vs[4],
            'protein' => $vs[5],
            'carbs' => $vs[6],
            'fat' => $vs[7],
            'dietary_tags' => 'Món chay, Thanh đạm, Eat Clean, Ít chất béo, Tốt cho tim mạch',
            'image_url' => $imgMap[$vb[1]] ?? $imgMap['chay']
        ];
    }
}
echo "Đã sinh tổng cộng " . count($recipesToInsert) . " công thức sau nhóm Món Chay.\n";

// Trim or slice to exact target ~2000 recipes
if (count($recipesToInsert) > $targetCount) {
    $recipesToInsert = array_slice($recipesToInsert, 0, $targetCount);
}

echo "Tổng số công thức sẽ được nạp vào cơ sở dữ liệu: " . count($recipesToInsert) . "\n";

// Insert in batches of 100 with transaction
$stmt = $db->prepare("
    INSERT INTO recipes (
        id, title, description, category, cooking_time, servings,
        ingredients, instructions, tips, calories, protein, carbs, fat,
        dietary_tags, image_url, author_id, status, views_count, likes_count,
        created_at, updated_at
    ) VALUES (
        :id, :title, :description, :category, :cooking_time, :servings,
        :ingredients, :instructions, :tips, :calories, :protein, :carbs, :fat,
        :dietary_tags, :image_url, :author_id, 'approved', 0, 0,
        NOW() - INTERVAL :days_ago DAY, NOW()
    )
");

$db->beginTransaction();
$inserted = 0;
$authorIndex = 0;
$daysAgo = 180;

foreach ($recipesToInsert as $r) {
    $author = $authorIds[$authorIndex % count($authorIds)];
    $authorIndex++;

    $stmt->execute([
        ':id' => $currentId,
        ':title' => $r['title'],
        ':description' => $r['description'],
        ':category' => $r['category'],
        ':cooking_time' => $r['cooking_time'],
        ':servings' => $r['servings'],
        ':ingredients' => $r['ingredients'],
        ':instructions' => $r['instructions'],
        ':tips' => $r['tips'],
        ':calories' => $r['calories'],
        ':protein' => $r['protein'],
        ':carbs' => $r['carbs'],
        ':fat' => $r['fat'],
        ':dietary_tags' => $r['dietary_tags'],
        ':image_url' => $r['image_url'],
        ':author_id' => $author,
        ':days_ago' => rand(1, 180)
    ]);

    $currentId++;
    $inserted++;

    if ($inserted % 200 === 0) {
        echo " -> Đã nạp thành công $inserted / " . count($recipesToInsert) . " món...\n";
    }
}

$db->commit();
echo "Hoàn thành 100%! Đã thêm thành công $inserted công thức món ăn mới vào hệ thống.\n";

