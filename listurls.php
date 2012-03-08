<?php
    require_once('configuration.php');
    check();
    $res = $db->query("SELECT * FROM `redirs` ORDER BY `id` DESC;");
    $ctnt = '';
    while ($row = $res->fetch_assoc())
    {
        $ctnt .= '<tr id="r_'.$row['id'].'"><td>';
        $id = getShortenedURLFromID($row['id']);
        $ctnt .= '<a href="http://saul.li/'.$id.'">'.$id.'</a>';
        $ctnt .= '</td><td class="url"><a href="';
        $ctnt .= $row['url'] . '">'.shortLongUrl($row['url']).'</a></td><td>'.$row['referrals'].'</td>';
        $ctnt .= '<td style="cursor:pointer;" onclick="remove(' . $row['id'] . ')"><img src="/x_small.png" alt="löschen" /></td>';
        $ctnt .= '</tr>';
    }
    header("Content-type: text/html; charset=UTF-8");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Shortened URLs</title>
    <link rel="stylesheet" href="/stylesheet.css" type="text/css" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js"></script>
    <script type="text/javascript">
        function remove(lid) {
            $.post('del_url.php', { id : lid }, function(data) {
                if (data['type'] == 'error') {
                    alert(data['msg']);
                } else if (data['type'] == 'success') {
                    $('#r_' + data['id']).hide();
                }
            });
        }
    </script>
</head>
<body>
    <div class="top"><a href="/"><img src="/saul.li%20logo.png" /></a></div>
    <div class="content">
        <h1>Shortened URLs</h1>
        <table class="listofurls">
        <tr><th>ID</th><th>URL</th><th>Clicks</th><th>&nbsp;</th></tr>
        <?php echo $ctnt; ?>
        </table>
        <p class="information">Proudly powered by <a href="http://tjark.saul.li/">Tjark Saul</a>.</p>
    </div>
</body>
</html>