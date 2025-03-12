<?php
session_start();

// Include config
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/offices.php';

$config = include __DIR__ . '/../config/config.php';
$offices = include __DIR__ . '/../config/offices.php';

// echo '<pre> offices Data:';
// var_dump($offices);
// echo '</pre>';
// echo '<pre> offices Data:';
// var_dump($offices[11]);
// echo '</pre>';


// Xác định office_id từ tham số GET
$office_id = $_GET['office'] ?? '';

// Kiểm tra office_id hợp lệ
if (!isset($offices[$office_id])) {
    // Office ID không hợp lệ, xử lý lỗi hoặc chuyển hướng (ví dụ: trang lỗi 404)
    // Ở đây đơn giản là hiển thị thông báo lỗi
    die("Office ID không hợp lệ.");
}


$office_config = $offices[$office_id];
// echo '<pre> offices Data:';
// var_dump($offices[$office_id]);
// echo '</pre>';
$mail_sys = $office_config['mail_sys'];
$office_name = $office_config['office_name'];
$page_title = $office_config['page_title'];

$error = [];
// hanlde form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $content = trim($_POST['content'] ?? '');

    // Validation 
    if (empty($name)) {
        $error['name'] = 'お名前を入力してください。';
    }
    if (empty($email)) {
        $error['email'] = 'メールアドレスを入力してください';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error['email'] = 'メールの形式が正しくありません';
    }
    if (empty($content)) {
        $error['content'] = '記載内容を入力してください';
    }

    // echo '<pre> Files data : ';
    // var_dump($_FILES);
    // echo '</pre>';
    if (isset($_FILES['attachment']) && !empty($_FILES['attachment']['name'][0])) {

        $uploadFiles = $_FILES['attachment'];
        $totalFilesSize = 0;

        // echo '<pre> Files detail : ';
        // var_dump($uploadFiles);
        // echo '</pre>';
        foreach ($uploadFiles['error'] as $key => $errorType) {
            // echo '<pre> data :';
            // var_dump($key);
            // echo '</pre>';
            if ($errorType === UPLOAD_ERR_OK) {
                $fileSize = $uploadFiles['size'][$key];
                $fileMimeType = mime_content_type($uploadFiles['tmp_name'][$key]);
                // Get file name to display in error message
                $fileName = $uploadFiles['name'][$key];

                $totalFilesSize += $fileSize;

                if ($totalFilesSize > $config['max_file_size']) {
                    $error['attachment'] = "合計サイズは " . formatFileSize($config['max_file_size']) . "を超えています";
                    break;
                }

                if (!in_array($fileMimeType, $config['allowed_mime_types'])) {
                    $error['attachment'] = "ファイル形式 '{$fileName}' は許可されません。";
                    break;
                }
            } else {
                switch ($errorType) {
                    case UPLOAD_ERR_INI_SIZE:
                        $error['attachment'] = "ファイル '{$uploadFiles['name'][$key]}' . が大きすぎます";
                        break;
                    case UPLOAD_ERR_PARTIAL:
                        $error['attachment'] = "ファイル '{$uploadFiles['name'][$key]}'. のアップロードが中断されます。";
                        break;
                    case UPLOAD_ERR_NO_TMP_DIR:
                        $error['attachment'] = "サーバーに一時フォルダが設定されませんでした。";
                        break;
                    case UPLOAD_ERR_CANT_WRITE:
                        $error['attachment'] = "ファイル '{$uploadFiles['name'][$key]}'をディスクに書き込めませんでした。";
                        break;
                    default:
                        $error['attachment'] = "ファイル '{$uploadFiles['name'][$key]}'のアップロード中に不明なエラーが発生しました。";
                        break;
                }
                break;
            }
            // echo '<pre> data :';
            // var_dump($fileSize);
            // echo '</pre>';
        }
    }

    // save data to session if there is no error 
    if (empty($error)) {
        $_SESSION['form-data'] = [
            'name' => $name,
            'email' => $email,
            'content' => $content,
            'mail_sys' => $mail_sys,
            'office_id' => $office_id
        ];
        // save info file if there is no attachment error 
        if(isset($_FILES['attachment']) && empty($error['attachment'])){
            $uploadFilesInfo = [];
            $uploadFiles = $_FILES['attachment'];
            foreach($uploadFiles['error'] as $key => $errorType){
                if($errorType == UPLOAD_ERR_OK){
                    $uploadFilesInfo[] = [
                        'name' => $uploadFiles['name'][$key],
                        'type' => mime_content_type($uploadFiles['tmp_name'][$key]),
                        'size' => $uploadFiles['size'][$key],
                        'tmp_name' => $uploadFiles['tmp_name'][$key]
                    ];
                }
            }
            $_SESSION['uploaded_files'] = $uploadFilesInfo;
            echo '<pre> files data :';
            var_dump($_SESSION);
            echo '</pre>';
        }else{
            // Delete session files if there are no file or there is an attachment error
            unset($_SESSION['uploaded_files']); 
        }
        // ... (chúng ta sẽ thêm xử lý file và chuyển hướng sau) ...
        header("location:../confirm.php");
        echo "Form hợp lệ, dữ liệu đã được lưu vào session. (Chưa chuyển hướng)"; // Thông báo tạm thời
    }
}

?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
</head>

<body class="bg-gray-100">
    <div class="container mx-auto p-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-4"><?php echo htmlspecialchars($office_name); ?></h1>
        <p class="text-gray-600 mb-6">※工事をする際の注意点等を記載願います。</p>

        <form action="form.php?office=<?php echo htmlspecialchars($office_id); ?>" method="POST" enctype="multipart/form-data" class="bg-white shadow-md rounded-lg p-8">
            <div class="mb-4">
                <label for="name" class="block text-gray-700 text-sm font-bold mb-2">お名前 <span class="text-red-500">*</span></label>
                <input type="text" id="name" name="name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                <?php if (isset($error['name'])) : ?>
                    <p class="text-red-500 text-xs italic"><?php echo $error['name']; ?></p>
                <?php endif; ?>
            </div>

            <div class="mb-4">
                <label for="email" class="block text-gray-700 text-sm font-bold mb-2">メールアドレス <span class="text-red-500">*</span></label>
                <input type="email" id="email" name="email" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                <?php if (isset($error['email'])) : ?>
                    <p class="text-red-500 text-xs italic"><?php echo $error['email'] ?></p>
                <?php endif ?>
            </div>

            <div class="mb-4">
                <label for="content" class="block text-gray-700 text-sm font-bold mb-2">記載内容 <span class="text-red-500">*</span></label>
                <textarea id="content" name="content" rows="5" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
                <?php if (isset($error['content'])) : ?>
                    <p class="text-red-500 text-xs italic"><?php echo $error['content'] ?></p>
                <?php endif ?>
            </div>

            <div class="mb-6">
                <label for="attachment" class="block text-gray-700 text-sm font-bold mb-2">添付ファイル</label>
                <input type="file" id="attachment" name="attachment[]" multiple class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                <p class="text-gray-500 text-xs italic">アップロード可能形式ファイル : Png, Jpg, PDF, Word, Excel, PowerPoint, Zip. 合計サイズは: 最大<?php echo formatFileSize($config['max_file_size']); ?>まで.</p>
                <?php if (isset($error['attachment'])) : ?>
                    <p class="text-red-500 text-xs italic"><?php echo $error['attachment'] ?></p>
                <?php endif ?>
            </div>

            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="button">
                    内容を確認する
                </button>
                <a href="#" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
                    リセット
                </a>
            </div>
        </form>
    </div>
</body>

</html>

<?php
function formatFileSize($bytes)
{
    if ($bytes >= 1048576) {
        return round($bytes / 1048576, 1) . ' MB';
    } elseif ($bytes >= 1024) {
        return round($bytes / 1024, 1) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}
?>