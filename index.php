<?php
// ********** إعداد قاعدة البيانات **********
$mysqli = new mysqli("localhost", "root", "", "shortener");
if ($mysqli->connect_error) die("Database Error");

// ********** إنشاء الجدول إذا لم يكن موجود **********
$mysqli->query("
  CREATE TABLE IF NOT EXISTS links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    short VARCHAR(20) UNIQUE,
    long TEXT,
    clicks INT DEFAULT 0
  )
");

// ********** معالجة الإدخال **********
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['longurl'])) {
    $long = $mysqli->real_escape_string($_POST['longurl']);
    $short = substr(md5(time()), 0, 6);
    $mysqli->query("INSERT INTO links (short, long) VALUES ('$short', '$long')");
    $shorturl = "http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."?c=$short";
    echo "<h3>تم الاختصار:</h3>";
    echo "<a href='$shorturl'>$shorturl</a>";
    exit;
}

// ********** التوجيه **********
if (isset($_GET['c'])) {
    $code = $mysqli->real_escape_string($_GET['c']);
    $res = $mysqli->query("SELECT long, clicks FROM links WHERE short='$code'");
    if ($res && $row = $res->fetch_assoc()) {
        $target = $row['long'];
        $mysqli->query("UPDATE links SET clicks=clicks+1 WHERE short='$code'");
        // صفحة الإعلان قبل التوجيه
        ?>
        <!DOCTYPE html>
        <html lang="ar">
        <head>
          <meta charset="UTF-8">
          <meta http-equiv="refresh" content="5;url=<?=$target?>">
          <title>يرجى الانتظار</title>
          <style>
            body { text-align:center; font-family:Arial; }
            .ad { margin:20px; }
          </style>
        </head>
        <body>
          <h2>سيتم تحويلك بعد 5 ثواني...</h2>
          <div class="ad">
            <!-- هنا تحط كود اعلانك -->
            <img src="https://via.placeholder.com/300x250?text=Ad+Banner" />
          </div>
          <p><a href="<?=$target?>">تخطي الإعلان الآن</a></p>
        </body>
        </html>
        <?php
        exit;
    } else {
        echo "الرابط غير موجود";
        exit;
    }
}

// ********** واجهة الإدخال **********
?>
<!DOCTYPE html>
<html lang="ar">
<head>
  <meta charset="UTF-8">
  <title>cutlink</title>
  <style>
    body { text-align:center; font-family:Arial; }
    input, button { padding:10px; margin:10px; width:300px; }
  </style>
</head>
<body>
  <h1>Cut your ties</h1>
  <form method="post">
    <input type="url" name="longurl" placeholder="أدخل الرابط هنا" required>
    <button type="submit">cut short</button>
  </form>
</body>
</html>
