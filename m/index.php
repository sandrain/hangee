<?php
// index.php
require $_SERVER['DOCUMENT_ROOT'].'/hangee/m/lib/__core.php';

$page = new Page();
$page->setTitle('HanGEE');
$page->setMaster('m');
$page->setScript('/hangee/m/index.js');

ob_start();
?>

<div id="profileinput" class="hidden">
    <div id="intro" class="textbox">
        <p>
        이 프로그램은 GRE 시험을 준비하는 한국 학생들을
        위해 제작되었으며, 무료로 배포됩니다.
        </p>
        <p>
        이 프로그램은 해커스 게시판에서 구할 수 있는 '한지'와 '거만어'를 이용합니다.
        혹 무단 사용으로 인한 법률적인 문제가 있을 경우 연락을 주시면 조치하겠습니다.
        </p>
        <p>
        이 메세지는 처음 프로그램을 실행하신 경우에만 표시됩니다.
        </p>
    </div>
    <div id="inputname">
        <p>사용자 이름을 입력하세요.</p>
        <div class="left">
            <input type="text" id="username" value="최대 10자, 예)예쁜이" onclick="this.value='';">
        </div>
        <div class="right">
            <a class="button" href="javascript:void(0)" onclick="saveUserName()">입력</a>
        </div>
    </div>
</div>

<div id="currentprofile" class="hidden">
    <div id="welcome" class="textbox">
        <p><span id="profilename"></span>님, 반갑습니다. <br />
        현재 학습 진행 상황은 다음과 같습니다.</p>
        <div id="studyprogress">
            <table>
                <tr>
                    <th>한지</th>
                    <td><span id="hancount"></span> / 4532 개</td>
                    <td><span id="hanrate"></span> %</td>
                </tr>
                <tr>
                    <th>거만어</th>
                    <td><span id="gocount"></span> / 2340 개</td>
                    <td><span id="gorate"></span> %</td>
                </tr>
            </table>
        </div>
        <p onclick="navigateHelp()" class='linktext'>
        도움말 및 프로그램 정보는 여기를 클릭해 확인하세요.
        </p>
    </div>
</div>

<div id="etclink">
</div>

<?php
$page->setContent(ob_get_contents());
ob_clean();

$page->render();
?>
