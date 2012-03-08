<?php
    require_once('configuration.php');
    $arr = array();
    if (!checkBool())
    {
        $arr['type'] = 'error';
        $arr['msg'] = 'Keine Berechtigung';
    }
    else
    {
        if (!isset($_POST['id']) || !is_numeric($_POST['id']))
        {
            $arr['type'] = 'error';
            $arr['msg'] = 'Keine ID angegeben.';
        }
        else
        {
            $id = (int)$_POST['id'];
            $db->query("DELETE FROM `redirs` WHERE `id` = $id LIMIT 1;");
            if ($db->error)
            {
                $arr['type'] = 'error';
                $arr['msg'] = 'Beim Schreiben in die Datenbank ist ein Fehler aufgetreten, bitte versuche es später noch einmal.';
            }
            else
            {
                $arr['type'] = 'success';
                $arr['id'] = $id;
            }
        }
    }
    header("Content-type: application/json");
    echo json_encode($arr);
    exit;
?>