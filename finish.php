<?php
session_start();

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

// check session , if no form data , return form
if (!isset($_SESSION['form-data'])) {
    $office_id = $_SESSION['form-data']['office_id'] ?? '11';
    header("location: form/form.php?office={$office_id}");
    exit();
}

// Get data form session 
$formData = $_SESSION['form-data'];
$uploadedFiles = $_SESSION['uploaded_files'] ?? [];

$office_id = $formData['office_id'];
$mail_to = $formData['mail_sys'];

$mail_subject = "ウェーブサイトからの記載がありました。";
$mail_body_admin = "{$mail_subject}\n\n";
$mail_body_admin .= "お名前: {$formData['name']} \n";
$mail_body_admin .= "メールアドレス: {$formData['email']} \n";
$mail_body_admin .= "記載内容: {$formData['content']} \n\n";

if (!empty($uploadedFiles)) {
    $mail_body_admin .= "[添付ファイル]\n";
    foreach ($uploadedFiles as $fileInfo) {
        $mail_body_admin .= "-{$fileInfo['name']} ({$fileInfo['type']}), " . formatFileSize($fileInfo['size']) . ")\n";
    }
} else {
    $mail_body_admin .= "[添付ファイルなし]\n";
}

$mail_form = "";
$mail_header = "From:" . mb_encode_mimeheader("ウェーブサイト") . "<$mail_form>";
$mail_header .= "Reply-To: " . $formData['email'] . "\n";
$mail_header .= "Content-Type: text/plain; charset=UTF-8\n";
$mail_header .= "Content-Transfer-Encoding: 7bit";




$mail_result = mail($mail_to, mb_encode_mimeheader($mail_subject), $mail_body_admin, $mail_header);

if ($mail_result) {
    $mail_status_message = "メール送信が完了しました。";
} else {
    $mail_status_message = "メール送信に失敗しました。";
}


unset($_SESSION['form-data']);
unset($_SESSION['uploaded_files']);
session_destroy();


// echo '<pre> confirm data : ';
// var_dump($_SESSION);
// echo '</pre>';


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <title>送信完了</title>
</head>

<body class="bg-gray-100">
    <div class="container mx-auto p-8 text-center">
        <h1 class="text-3xl font-bold text-green-600 mb-4">送信完了しました。</h1>
        <p class="text-gray-700 mb-8">記載頂き、ありがとうございます</p>
        <p class="text-gray-700 mb-8"><?php echo $mail_status_message; ?></p> <!-- Hiển thị thông báo trạng thái email (thành công/thất bại) -->
        <a href="form/form.php?office=<?php echo htmlspecialchars($office_id); ?>" class="inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
            フォームへ戻る
        </a>
    </div>
</body>

</html>