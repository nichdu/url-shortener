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
        if (CACHE)
        {
            $url = file_get_contents(CACHE_DIR . $id);
            if(empty($url) || !preg_match('|^https?://|', $url))
            {
                $q = "SELECT `url` FROM `redirs` WHERE `id` = '$id';";
                $res = $db->query($q);
                if ($res->num_rows == 0 || $db->error)
                {
                    die(htmlspecialchars($_GET['identifier']) . ' ist kein gültiger URL-Identifier.');
                }
                $row = $res->fetch_assoc();
                $url = $row['url'];
                @mkdir(CACHE_DIR, 0777);
                $handle = fopen(CACHE_DIR . $id, 'w+');
                fwrite($handle, $url);
                fclose($handle);
            }
        }
        else
        {
            $q = "SELECT `url` FROM `redirs` WHERE `id` = '$id';";
            $res = $db->query($q);
            if ($res->num_rows == 0 || $db->error)
            {
                die(htmlspecialchars($_GET['identifier']) . ' ist kein gültiger URL-Identifier.');
            }
            $row = $res->fetch_assoc();
            $url = $row['url'];
        }
        if(TRACK)
        {
            $db->query("UPDATE redirs SET referrals=referrals+1 WHERE id = '$id';");
        }
        //header('HTTP/1.1 301 Moved Permanently');
        header("Location: $url");
    }
    else if (isset($_REQUEST['url']))
    {
        check();
        if (empty($_REQUEST['url'])) { die('Bitte eine gültige URL angeben.'); }
        if (substr($_REQUEST['url'], 0, 4) != 'http')
        {
            $_REQUEST['url'] = 'http://' . $_POST['url'];
        }
        $url = $db->real_escape_string($_REQUEST['url']);
        $q1 = "SELECT `id` FROM `redirs` WHERE `url` = '$url';";
        $r = $db->query($q1);
        if ($r->num_rows == 0)
        {
            $db->query("LOCK TABLES `redirs` WRITE;");
            $q = "INSERT INTO `redirs`(`url`) VALUES('$url');";
            $res = $db->query($q);
            if ($db->error) { die('Fehler'); }
            $u = getShortenedURLFromID($db->insert_id);
            $db->query("UNLOCK TABLES;");
        }
        else
        {
            $row = $r->fetch_array();
            $u = getShortenedURLFromID($row[0]);
        }
        if (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
        {
            print("<a href=\"http://saul.li/$u\">http://saul.li/$u</a>");
        }
        else
        {
            print("http://saul.li/$u");
        }
    }
    else
    {
        $res = $db->query("SELECT COUNT(`id`) FROM `redirs`;");
        $row = $res->fetch_array();
        $num = $row[0];
        ?>
<!DOCTYPE html>
<html>
<head>
    <title>URL-Shortener</title>
    <link rel="stylesheet" href="/stylesheet.css" type="text/css" />
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js"></script>
    <script type="text/javascript" src="/jquery.form.js"></script>
    <script type="text/javascript" src="/spinners.min.js"></script>
    <script type="text/javascript">
        var options = {
            target: '#return_c',
            beforeSubmit: function(arr, $form, options) {
                Spinners.create('#return_c');
                Spinners.create('#return_c').play();
                return true;
            },
            resetForm: true,
            clearForm: true,
            success: function(data) {
                $('#return').show();
                return true;
            } 
        }
        
        $(document).ready(function() {
            $('#ajaxform').ajaxForm(options);
            $('#url').focus();
        });
        
        function saveForm()
        {
            $('#ajaxform').ajaxSubmit(options);
            return false;
        }
        
        function rl()
        {
            if($('#customurl_enabled').is(':checked'))
            {
                $('#customurl').removeAttr('disabled');
                $('#custom').show();
            }
            else
            {
                $('#customurl').attr('disabled', true);
                $('#custom').hide();
            }
        }
    </script>
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
</head>
<body>
    <div class="top"><a href="/"><img src="/saul.li%20logo.png" /></a></div>
    <div class="content">
        <h1>URL Shortener</h1>
        <p style="display:none;" id="return">URL: <span id="return_c"></span></p>
        <form method="post" accept-charset="utf-8" id="ajaxform" action="/index.php">
            <fieldset class="aussen">
                <legend>Content</legend>
                <fieldset class="innen">
                    <legend>URL</legend>
                    <label for="url">URL:&nbsp;</label><input type="url" id="url" name="url" size="50" maxlength="512" /><br />
<?php if (false) { ?><!-- <input type="checkbox" name="customurl_enabled" id="customurl_enabled" value="1" onclick="rl()" /><label for="customurl_enabled">&nbsp;Custom URL</label><br /> -->
                </fieldset>
                <fieldset id="custom" class="innen">
                    <legend>URL preferences</legend>
                    <label for="customurl">Custom URL:&nbsp;</label><input type="text" id="customurl" name="customurl" size="6" maxlength="6" tabindex="1" disabled="disabled" /><br /><?php } ?>
                </fieldset>
                <input type="submit" value="Speichern" />
                <!-- <a href="#" onclick="saveForm(); return false;">Absenden</a> -->
            </fieldset>
        </form>
        <p class="information">Database: <a href="/listurls.php"><?php echo $num; ?> URLs</a> since 12/03/03. <br/>Proudly powered by <a href="http://tjark.saul.li/">Tjark Saul</a>.</p>
    </div>
</body>
</html><?php
    }
?>