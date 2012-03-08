<?php
    require_once('configuration.php');
    header('Content-type: text/html; charset=UTF-8');
    if (isset($_GET['identifier']))
    {
        if(!preg_match('|^[0-9a-zA-Z]{1,6}$|', $_GET['identifier']))
        {
            die(htmlspecialchars($_GET['identifier']) . ' ist kein gültiger URL-Identifier.');
        }
        $id = getIDFromShortenedURL($_GET['identifier']);
        $lid = htmlspecialchars($_GET['identifier']);
        $q = "SELECT `url`, `referrals` FROM `redirs` WHERE `id` = '$id';";
        $res = $db->query($q);
        if ($res->num_rows == 0 || $db->error)
        {
            die(htmlspecialchars($_GET['identifier']) . ' ist kein gültiger URL-Identifier.');
        }
        $row = $res->fetch_assoc();
        $url = $row['url'];
        $ref = $row['referrals'];
    }
    else
    {
        header("Location: http://saul.li");
    }
?>
<!DOCTYPE html>
<html>
<head>
    <title>URL Statistics</title>
    <link rel="stylesheet" href="/stylesheet.css" type="text/css" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
</head>
<body>
    <div class="top"><a href="/"><img src="/saul.li%20logo.png" /></a></div>
    <div class="content">
        <h1>URL Statistics</h1>
        <p class="urlinfo">The short URL <a href="http://saul.li/<?php echo $lid; ?>">http://saul.li/<?php echo $lid; ?></a> 
        redirects to <a href="http://saul.li/<?php echo $lid; ?>"><?php echo $url; ?></a> and has been clicked <?php echo $ref; ?> times.</p>
        <p class="information">Proudly powered by <a href="http://tjark.saul.li/">Tjark Saul</a>.</p>
    </div>
</body>
</html>