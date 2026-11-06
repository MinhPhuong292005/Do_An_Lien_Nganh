#  Cookio - Mạng Xã Hội Ẩm Thực & Nền Tảng Chia Sẻ Công Thức Bếp Gia Đình

> **Cookio** là nền tảng ẩm thực gia đình hiện đại, ấm cúng và thông minh theo chuẩn **Cookpad**, **Tasty** và **Yummly**, được xây dựng bằng **PHP thuần**, **MySQL/MariaDB**, **JavaScript (ES6+)** và **CSS hiện đại**.
> Hệ thống mang đến trải nghiệm nấu ăn thông minh, kết nối cộng đồng yêu bếp, quản lý thực đơn, đi chợ tiện lợi và tích hợp cổng quản trị nội dung toàn diện.

---

##  Tính Năng Nổi Bật

### 1.  Kho Dữ Liệu Lớn & Ảnh Thực Phẩm Đẹp Mắt
- **Gần 2,000 công thức nấu ăn phong phú**: Đa dạng các món ăn truyền thống 3 miền Bắc - Trung - Nam, món Á - Âu, món chay thanh tịnh, món ăn kiêng Healthy & Eat Clean.
- **Bộ sưu tập hình ảnh thực phẩm chất lượng cao**: Toàn bộ món ăn hiển thị ảnh chụp thật chân thực, sắc nét (thay thế hoàn toàn ảnh SVG/placeholder minh họa).
- **Quy chuẩn đăng bài nghiêm ngặt**: Yêu cầu bắt buộc tải lên ảnh thành phẩm thực tế của món ăn (`required`), kiểm tra định dạng và kích thước an toàn.
- **51 bài viết Kinh Nghiệm Hay & Mẹo Vặt Nhà Bếp**: Kho tàng bí quyết nấu nướng, sơ chế, khử mùi tanh, bảo quản thực phẩm tươi lâu và nêm nếm gia vị chuẩn vị.

### 2.  Giao Diện Bếp Nhà & Banner Tự Trượt Hiện Đại (Hero Carousel)
- **Banner trượt mượt mà (3 slide chuyên đề)**: Tự động chuyển đổi sau mỗi 5 giây, tạm dừng khi rê chuột, có nút điều hướng mũi tên và chấm tròn phân trang:
  - *Bếp Nhà Sum Vầy*: Tìm kiếm thông minh, thẻ tag thịnh hành & tính năng quay ngẫu nhiên *"Hôm nay ăn gì?"*.
  - *Tủ Lạnh Thông Minh 2.0*: Gợi ý món ngon từ nguyên liệu sẵn có trong tủ lạnh.
  - *Sổ Tay Thực Đơn 7 Ngày*: Menu cả tuần cân bằng dinh dưỡng, gom nguyên liệu đi chợ trong 1 cú nhấp.
- **Chế độ Bếp Ban Đêm (High-Contrast Dark Mode)**: Chuyển đổi giao diện sáng/tối chỉ với 1 cú click, bảo vệ thị lực khi nấu ăn vào buổi tối.
- **Tìm kiếm trực tiếp (Live Search / Quick Search API)**: Gợi ý công thức tức thì ngay khi gõ tên món, tác giả hoặc nguyên liệu.

### 3.  Tủ Lạnh Thông Minh 2.0 (Smart Fridge Recipe Matcher)
- **Nấu ngon từ nguyên liệu sẵn có**: Người dùng chọn các nguyên liệu đang có trong bếp (thịt bò, thịt heo, cá, tôm, trứng, rau củ,...).
- **Thuật toán gợi ý thông minh**: Đếm số nguyên liệu trùng khớp, tính toán phần trăm độ phù hợp và sắp xếp ưu tiên các món có thể nấu ngay nhằm chống lãng phí thực phẩm.
- **Gợi ý mua bổ sung**: Liệt kê rõ những gia vị hoặc nguyên liệu phụ còn thiếu để hoàn thiện món ăn.

### 4.  Món Đã Lưu & Đi Chợ Thông Minh (Smart Grocery Checklist)
- **Quản lý món yêu thích & thả tim**:
  - Lưu món ăn để xem lại (`Bookmark`) và Thả tim (`Like`).
  - Thao tác thả tim / bỏ thích nhanh chóng chỉ với 1-Click trực tiếp.
  - Thêm ghi chú bếp cá nhân riêng cho từng món đã lưu.
- **Thanh gom nguyên liệu đi chợ thông minh (Floating Action Bar)**:
  - Cho phép tích chọn một hoặc nhiều món đã lưu, hoặc nút **"Chọn tất cả món"**.
  - Tự động bóc tách và tổng hợp danh sách nguyên liệu cần mua thành bảng kiểm (checklist) thông minh.
  - Hỗ trợ đánh dấu đã mua và nút **"Sao chép danh sách đi chợ"** 1-Click để gửi qua tin nhắn/Zalo.

### 5.  Trải Nghiệm Nấu Nướng Thực Tế Đỉnh Cao
- **Chế độ nấu rảnh tay (Hands-Free Cooking Mode)**: Giao diện toàn màn hình với cỡ chữ lớn, tối ưu thao tác khi tay dính nước hoặc dầu mỡ trong bếp.
- **Đồng hồ bấm giờ nhà bếp (Kitchen Timer)**: Tích hợp bộ đếm giờ thông minh có âm thanh chuông báo Web Audio API khi đến giờ tắt bếp.
- **Bộ điều chỉnh khẩu phần động (Servings Scaler)**: Tự động nhân/chia định lượng nguyên liệu tương ứng theo số người ăn (1 người, 2 người, 4 người, mâm cỗ 8 người,...).
- **In công thức chuẩn khổ giấy (Print Recipe)**: Tự động loại bỏ các thành phần web thừa, tối ưu bản in sạch đẹp để mang vào gian bếp.

### 6.  Tương Tác Cộng Đồng & Trung Tâm Thông Báo Thời Gian Thực
- **Hệ thống Thông báo thông minh (Phân quyền riêng biệt)**:
  - Nhận thông báo khi có người thả tim, bình luận, gửi ảnh Cooksnap hoặc có người theo dõi mới.
  - **Thông báo kiểm duyệt nội dung**: Khi Admin duyệt bài, nhấp vào thông báo sẽ mở ngay công thức tương ứng. Khi Admin từ chối duyệt bài, hệ thống hiển thị popup modal giải thích lịch sự, ngắn gọn và trang nhã ở chính giữa màn hình: *"Rất tiếc, bài đăng không được duyệt"*.
- **Chuỗi Thảo luận Đa Tầng (Threaded Author Q&A)**:
  - Cho phép người nấu đặt câu hỏi và tác giả phản hồi trực tiếp kèm huy hiệu danh dự **Tác giả / Đầu bếp**.
- **Đăng ảnh thực hành Cooksnap**: Tải ảnh chụp món ăn tự tay hoàn thành để chia sẻ và nhận xét cho chủ công thức.
- **Đánh giá & Xếp hạng 1 - 5 sao**: Chấm điểm chất lượng công thức nấu ăn minh bạch.

### 7.  Trang Cá Nhân & Hồ Sơ Gian Bếp (Profile & Author Studio)
- **Thanh danh mục nằm gọn trên 1 dòng**: Cuộn mượt các mục *Công thức đã đăng*, *Theo dõi*, *Đang theo dõi*, *Đánh giá & Nhận xét*.
- **Bảng chỉ số hoạt động tinh tế**: Thống kê chính xác số *công thức món*, *theo dõi*, *đang theo dõi*, *lượt thích* và *điểm đánh giá*.
- **Cập nhật hồ sơ & đổi ảnh đại diện (Avatar)**: Tùy chỉnh họ tên, giới thiệu tiểu sử (bio), số điện thoại và ảnh đại diện nhanh chóng.

### 8.  Sổ Tay Thực Đơn Tuyển Chọn (Curated Cookbooks)
- Các bộ sưu tập thực đơn đa dạng:
  - *Thực đơn cơm gia đình 7 ngày sum vầy*.
  - *Thực đơn Eat Clean & Giảm cân thanh lọc cơ thể*.
  - *Món ngon cuối tuần & Tụ tập bạn bè*.
  - *Bữa sáng cấp tốc tràn đầy năng lượng dưới 15 phút*.
- Thống kê tổng quan: Tổng số món, tổng thời gian nấu ước tính và lượng calo của toàn bộ menu.

### 9.  Cổng Quản Trị Hệ Thống Toàn Diện (Admin Portal - `/admin/`)
- **Bảng điều khiển Tổng quan (Dashboard)**: Thống kê số lượng công thức, người dùng, bình luận, bài mẹo và các công thức đang chờ duyệt.
- **Quản lý công thức (`manage-recipes.php`)**:
  - Xem chi tiết nhanh, phê duyệt hoặc từ chối bài viết kèm lý do.
  - Phân trang thông minh (`1, 2, 3 ... 79, 80`).
  - Tùy chọn hiển thị số lượng bài trên mỗi trang linh hoạt: `25`, `30`, `50`, `100` món/trang.
  - Bộ lọc đa năng theo trạng thái (*Chờ duyệt*, *Đã duyệt*, *Từ chối*) và ô tìm kiếm tức thì theo ID, tên món hoặc tác giả.
- **Quản lý kinh nghiệm hay (`manage-tips.php`)**: Quản lý toàn bộ 51 bài viết mẹo bếp, xem lượt đọc, sửa nội dung, xóa bài hoặc đăng bài mẹo mới từ admin.
- **Quản lý danh mục (`manage-categories.php`)**: Thêm mới, chỉnh sửa icon, tên danh mục, đường dẫn URL, sắp xếp thứ tự hiển thị và ẩn/hiện danh mục.
- **Quản lý bình luận & đánh giá (`manage-comments.php`)**: Xem danh sách phản hồi, số sao chấm điểm, tìm kiếm theo từ khóa và xóa các bình luận vi phạm tiêu chuẩn cộng đồng.
- **Quản lý tài khoản (`manage-users.php`)**: Quản lý danh sách thành viên, phân quyền vai trò (Admin, Author, User), khóa hoặc mở khóa tài khoản người dùng vi phạm.

---

##  Cấu Trúc Thư Mục Dự Án (Clean Architecture)

Mã nguồn được tổ chức khoa học, tách bạch rõ ràng giữa xử lý logic, giao diện và cơ sở dữ liệu:

```
Cookio/
├── actions/                  # Xử lý các request POST/GET và nghiệp vụ (Controller/API)
│   ├── admin_action.php      # Nghiệp vụ duyệt bài, quản trị nội dung
│   ├── auth_action.php       # Đăng nhập, đăng ký thành viên
│   ├── badge_action.php      # Cập nhật huy hiệu đầu bếp
│   ├── comment_action.php    # Bình luận, trả lời Q&A, gửi ảnh Cooksnap
│   ├── cookbook_action.php   # Quản lý sổ tay thực đơn
│   ├── follow_action.php     # Theo dõi / Bỏ theo dõi tác giả
│   ├── like_action.php       # Thả tim / Bỏ thích công thức
│   ├── notification_action.php # Đánh dấu đã đọc thông báo
│   ├── profile_action.php    # Cập nhật thông tin cá nhân & avatar
│   ├── quick_search_api.php  # API tìm kiếm nhanh Ajax
│   ├── recipe_action.php     # Tạo món, sửa món, xóa công thức
│   ├── saved_action.php      # Lưu món, ghi chú bếp, checklist đi chợ
│   └── tip_action.php        # Đăng / sửa bài viết kinh nghiệm bếp
├── admin/                    # Cổng Quản trị Viên (Admin Portal)
│   ├── index.php             # Tổng quan dashboard thống kê
│   ├── manage-recipes.php    # Quản lý kiểm duyệt công thức
│   ├── manage-tips.php       # Quản lý 51 bài kinh nghiệm hay
│   ├── manage-categories.php # Quản lý danh mục ẩm thực
│   ├── manage-comments.php   # Quản lý bình luận & đánh giá sao
│   └── manage-users.php      # Quản lý tài khoản thành viên
├── assets/                   # Tài nguyên tĩnh
│   ├── css/                  # style.css, user.css, dark-theme.css
│   ├── js/                   # admin.js, enterprise-features.js, smart-fridge.js
│   └── images/
│       └── real_dishes/      # Kho ảnh chụp món ăn thật chất lượng cao
├── config/                   # Cấu hình hệ thống & môi trường
│   └── config.php            # Kết nối CSDL, đường dẫn Base URL, helper phân trang
├── includes/                 # Các module dùng chung & giao diện khung
│   ├── admin-sidebar.php     # Thanh điều hướng Admin
│   ├── badges.php            # Hệ thống danh hiệu thành viên
│   ├── categories_helper.php # Danh mục món ăn
│   ├── community_standards.php # Tiêu chuẩn cộng đồng Cookio
│   ├── db.php                # Kết nối PDO MySQL an toàn
│   ├── footer.php            # Chân trang Cookio
│   ├── header.php            # Đầu trang, thanh tìm kiếm & trung tâm thông báo
│   ├── modal-all-recipes.php # Khung khám phá toàn bộ món ăn
│   ├── modal-login.php       # Popup đăng nhập / đăng ký
│   └── search_helper.php     # Bộ lọc tìm kiếm nâng cao
├── sql/                      # Tệp sao lưu cơ sở dữ liệu
│   └── cookio_db.sql         # Toàn bộ cấu trúc bảng và dữ liệu mẫu chuẩn hóa
├── views/                    # Các trang hiển thị chính (Views)
│   ├── author.php            # Trang gian bếp của tác giả khác
│   ├── cookbooks.php         # Trang sổ tay thực đơn tuyển chọn
│   ├── create-recipe.php     # Trang đăng tải công thức mới (bắt buộc ảnh)
│   ├── create-tip.php        # Trang chia sẻ kinh nghiệm hay
│   ├── edit-recipe.php       # Trang chỉnh sửa công thức
│   ├── kitchen-tips.php      # Chuyên mục 51 bài mẹo vặt nhà bếp
│   ├── profile.php           # Trang cá nhân của tôi & gian bếp của tôi
│   ├── recipe-detail.php     # Chi tiết công thức & chế độ nấu rảnh tay
│   ├── saved-recipes.php     # Món đã lưu, món đã thả tim & gom đồ đi chợ
│   └── smart-fridge.php      # Trang Tủ Lạnh Thông Minh 2.0
├── docker-compose.yml        # Cấu hình khởi chạy Docker 1-Click
├── Dockerfile                # Image môi trường PHP Apache
├── index.php                 # Trang chủ Cookio
└── logout.php                # Xử lý đăng xuất an toàn
```

---

##  Hướng Dẫn Cài Đặt & Khởi Chạy

Bạn có thể chạy dự án theo 1 trong 2 cách dưới đây:

### Cách 1: Khởi Chạy Nhanh Bằng Docker (Khuyến nghị)
Hệ thống đã được đóng gói hoàn chỉnh gồm **PHP 8.2 Apache** và **MariaDB 10.6**, tự động nạp cơ sở dữ liệu `sql/cookio_db.sql`:

1. Đảm bảo máy tính đã cài đặt **Docker Desktop**.
2. Mở terminal tại thư mục gốc của dự án `Cookio` và chạy lệnh:
   ```bash
   docker compose up -d
   ```
3. Truy cập ngay vào ứng dụng tại:
   - **Website Cookio**: [http://localhost:8000](http://localhost:8000)
   - **Cổng Quản Trị Admin**: [http://localhost:8000/admin/index.php](http://localhost:8000/admin/index.php)

---

### Cách 2: Khởi Chạy Thủ Công (XAMPP / PHP Thuần)
1. **Khởi động dịch vụ MySQL** trong XAMPP Control Panel.
2. **Tạo và nạp Cơ Sở Dữ Liệu**:
   - Mở phpMyAdmin ([http://localhost/phpmyadmin](http://localhost/phpmyadmin)).
   - Tạo cơ sở dữ liệu mới có tên: `cookio_db` (bảng mã `utf8mb4_unicode_ci`).
   - Nhấn thẻ **Import** và chọn tệp: `sql/cookio_db.sql` để nạp dữ liệu.
3. **Cấu hình kết nối**:
   - Kiểm tra tệp `config/config.php` (mặc định user `root`, mật khẩu rỗng `""`, port `3306`).
4. **Chạy máy chủ phát triển PHP**:
   ```bash
   php -S localhost:8000
   ```
5. Mở trình duyệt và truy cập: **[http://localhost:8000](http://localhost:8000)**.

---

##  Tài Khoản Trải Nghiệm Mẫu

| Tài khoản | Mật khẩu | Vai trò | Mục đích trải nghiệm |
| :--- | :---: | :---: | :--- |
| **`admin1111`** | `1` | **Admin (Quản trị viên)** | Toàn quyền duyệt công thức, quản lý mẹo bếp, danh mục, bình luận và người dùng |
| **`chef_lan`** | `1` | **Author (Đầu bếp)** | Tài khoản tác giả có nhiều công thức, nhiều lượt thích, đánh giá sao và thông báo |
| **`me_bong`** | `1` | **Author (Thành viên)** | Tác giả các món cơm nhà truyền thống: Phở bò, Sườn xào chua ngọt, Canh ngao |
| **`chu_nam_cook`** | `1` | **Author (Thành viên)** | Tác giả các món đặc sản Nam Bộ: Canh chua cá lóc, Thịt kho hột vịt |
| **`lan_anh_kitchen`**| `1` | **Author (Thành viên)** | Tác giả các món Healthy, Salad ức gà, Thực đơn Eat Clean |

---

##  Tiêu Chuẩn Bảo Mật & Dữ Liệu Sạch

- **Không chứa mã Bot giả lập**: Toàn bộ dữ liệu tài khoản, bài viết, lượt thả tim và bình luận đều là tương tác thực tế từ người dùng, không chạy mã tự sinh ngẫu nhiên gây rác cơ sở dữ liệu.
- **Bảo vệ CSRF 100%**: Tất cả các thao tác thêm, sửa, xóa, duyệt bài (POST request) đều được xác thực bằng mã Token ngẫu nhiên theo phiên làm việc (`csrf_token()`).
- **An toàn Tải lên Tệp tin (File Upload)**: Kiểm tra MIME thực tế bằng `finfo`, kiểm soát định dạng ảnh cho phép (JPG, PNG, WEBP), giới hạn dung lượng tối đa 5MB và tự động đổi tên tệp an toàn chống tấn công Path Traversal.
- **Ngăn chặn SQL Injection**: 100% các truy vấn cơ sở dữ liệu được thực hiện thông qua **PDO Prepared Statements**.
- **Chống XSS (Cross-Site Scripting)**: Toàn bộ dữ liệu do người dùng nhập đều được xử lý qua hàm chuẩn hóa `e()` / `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')` trước khi render ra giao diện.

---

© 2026 Cookio Team. Nền tảng ẩm thực gia đình gắn kết triệu gian bếp Việt.
