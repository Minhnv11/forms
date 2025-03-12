<?php 
session_start();

$office_id = $_SESSION['form-data']['office_id'] ;

 if(!isset($_SESSION['form-data'])){
    header("location: form/form.php?office={$office_id}");
    exit();
 }

 $formData = $_SESSION['form-data'];
 $uploadedFiles = $_SESSION['uploaded_files'] ?? [];

//  echo '<pre> confirm data : ';
//  var_dump($_SESSION);
//  echo '</pre>';

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <title>確認画面</title>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-4">確認画面</h1>
        <p class="text-gray-600 mb-4">記載内容をご確認ください。</p>
        <div class="bg-white shadow-md rounded-lg p-8">
            <p class="mb-4">お名前: <?php echo htmlspecialchars($formData['name'] , ENT_QUOTES, 'UTF-8'); ?></p>
            <p class="mb-4">メールアドレス : <?php echo htmlspecialchars($formData['email'], ENT_QUOTES, 'UTF-8')?></p>
            <p class="mb-4">記載内容: <?php echo nl2br(htmlspecialchars($formData['content'], ENT_QUOTES, 'UTF-8')); ?></p>
            <?php if(!empty($uploadedFiles)) : ?>
            <p class="mb-2">添付ファイル:</p>
            <ul class="list-disc list-inside mb-4">
                <?php foreach($uploadedFiles as $fileInfo) :?>
                <li><?php echo htmlspecialchars($fileInfo['name'], ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach ?>
            </ul>
            <?php else : ?>
                <p class="mb-4">添付ファイル:添付ファイルなし</p>
            <?php endif ?>
            <div class="flex items-center justify-between my-3">
                <a class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:shadow-outline" href="finish.php">送信する</a>
                <a class="inline-block align-baseline font-bold text-blue-500 hover:text-blue-800" href="form/form.php?office=<?php echo $office_id?>">戻る</a>
            </div>
        </div>
    </div>
</body>
</html>