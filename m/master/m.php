<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title><?=$title;?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="apple-mobile-web-app-status-bar-style" content="black" />
<link rel="apple-touch-icon" href="http://sandrain.365managed.net/hangee/m/icons/HanGEE_57.png" />
<link rel="apple-touch-icon-precomposed" href="http://sandrain.365managed.net/hangee/m/icons/HanGEE_57.png" />
<link rel="apple-touch-startup-image" href="http://sandrain.365managed.net/hangee/m/icons/startup.png" />
<meta name="apple-mobile-web-app-capable" content="yes" />
<link rel="stylesheet" href="/hangee/m/m.css" type="text/css" />
<meta name="viewport" content="width=device-width,maximum-scale=1.0,minimum-scale=1.0,user-scalable=no" />
<script type="text/javascript" src="/scripts/js/jquery-1.6.1.min.js"></script>
<script type="text/javascript" src="/scripts/js/json2.js"></script>
<script type="text/javascript" src="/hangee/m/LocalDB.js"></script>
<script type="text/javascript" src="/hangee/m/core.js"></script>
<?=$headerContent?>
</head>
<body>
    <div id="wrapper">
        <div id="__pagedata" style="display:none">
            <input type="hidden" id="__hiddenpagedata" value='<?=$pageData?>' />
        </div>

        <div id="topmenu" class="hidden">
            <table>
                <tr>
                    <td id='menuYou' onclick='navigateYou()'>
                        <div class="icon">
                            <img src="/hangee/m/images/you.png" alt="Menu You icon" />
                        </div>
                        <div class="label">
                            <p>You</p>
                        </div>
                    </td>
                    <td id='menuStudy' onclick='navigateStudy()'>
                        <div class="icon">
                            <img src="/hangee/m/images/study.png" alt="Menu Study icon" />
                        </div>
                        <div class="label">
                            <p>Study</p>
                        </div>
                    </td>
                    <td id='menuHanGRE' onclick='navigateHanGRE()'>
                        <div class="icon">
                            <img src="/hangee/m/images/hangre.png" alt="Menu HanGRE icon" />
                        </div>
                        <div class="label">
                            <p>HanGRE</p>
                        </div>
                    </td>
                    <td id='menuGoMan' onclick='navigateGoMan()'>
                        <div class="icon">
                            <img src="/hangee/m/images/goman.png" alt="Menu GoMan icon" />
                        </div>
                        <div class="label">
                            <p>GoMan</p>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div id="content">

            <?=$mainContent?>

        </div>

        <div id="loading">
            <p>단어를 가져오는 중 입니다..</p>
        </div>

    </div>
</body>
</html>
