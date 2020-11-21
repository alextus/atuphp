<?php
/**
 *
 * @apiDefine RkNotFoundException
 *
 * @apiError RkNotFoundException 找不到相关数据
 *
 * @apiErrorExample Error-Response:
 *     HTTP/1.1 404 Not Found
 *     {
 *       "error": {
 *           "code": 404,
 *           "msg": "",
 *           "path" ""
 *       }
 *     }
 *
 */

/**
 *
 * @api {get} /test 测试接口
 * @apiVersion 1.1.0
 * @apiName test
 * @apiGroup test
 *
 * @apiParam {String} a 测试
 * @apiSuccessExample Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *       "sn": "P000000000",
 *       "status": 0,
 *       "soc": 80,
 *       "voltage": 60.0,
 *       "current": 10.0,
 *       "temperature": null,
 *       "reportTime": "2018-08-13 18:11:00"
 *     }
 *
 * @apiUse RkNotFoundException
 *
 */
function _get($txt)
{

    return isset($_GET[$txt]) ? SQLFilter(trim($_GET[$txt])) : "";
}

function _post($txt)
{
    return isset($_POST[$txt]) ? SQLFilter(trim($_POST[$txt])) : "";
}

function _cookie($txt)
{
    return isset($_COOKIE[$txt]) ? SQLFilter(trim($_COOKIE[$txt])) : "";
}
function S_get($txt)
{
    return _get($txt);
}
function S_post($txt)
{
    return _post($txt);
}
function S_cookie($txt)
{
    return _cookie($txt);
}
function _var($txt)
{
    return isset($txt) ? SQLFilter(trim($txt)) : "";
}
function SQLFilter($txt)
{
    if (is_numeric($txt)) {
        return $txt;
    }
    if (is_null($txt)) {
        return "";
    }
    if (!get_magic_quotes_gpc()) {
        $txt = addslashes($txt);
    }


    $txt = str_replace("script", "&#115;cript", $txt);
    $txt = str_replace("SCRIPT", "&#083;CRIPT", $txt);
    $txt = str_replace("Script", "&#083;cript", $txt);
    $txt = str_replace("script", "&#083;cript", $txt);
    $txt = str_replace("object", "&#111;bject", $txt);
    $txt = str_replace("OBJECT", "&#079;BJECT", $txt);
    $txt = str_replace("Object", "&#079;bject", $txt);
    $txt = str_replace("object", "&#079;bject", $txt);
    $txt = str_replace("applet", "&#097;pplet", $txt);
    $txt = str_replace("APPLET", "&#065;PPLET", $txt);
    $txt = str_replace("Applet", "&#065;pplet", $txt);
    $txt = str_replace("applet", "&#065;pplet", $txt);
    $txt = str_replace("select", "sel&#101;ct", $txt);
    $txt = str_replace("execute", "&#101xecute", $txt);
    $txt = str_replace("exec", "&#101xec", $txt);
    $txt = str_replace("join", "jo&#105;n", $txt);
    $txt = str_replace("union", "un&#105;on", $txt);
    $txt = str_replace("where", "wh&#101;re", $txt);
    $txt = str_replace("insert", "ins&#101;rt", $txt);
    $txt = str_replace("delete", "del&#101;te", $txt);
    $txt = str_replace("update", "up&#100;ate", $txt);
    $txt = str_replace("like", "lik&#101;", $txt);
    $txt = str_replace("drop", "dro&#112;", $txt);
    $txt = str_replace("create", "cr&#101;ate", $txt);
    $txt = str_replace("rename", "ren&#097;me", $txt);
    $txt = str_replace("exists", "e&#120;ists", $txt);
    $txt = str_replace("'", "&quot;", $txt);
    $txt = str_replace("`", "&quot;", $txt);

    return $txt;
}
