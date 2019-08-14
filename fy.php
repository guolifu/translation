<?php

/**
 * Class Ttranslate
 * author by carl
 */
class Translate
{
    /**
     * @var bool
     */
    public $debug = true;

    /**
     * @var string
     */
    public $help = 'demo: ttranslate.php "apple"';

    /**
     * @var string
     */
    public $urlFormat = 'http://fanyi.youdao.com/openapi.do'
    . '?keyfrom=%s&key=%s&type=data&doctype=json'
    . '&version=1.1&q=%s';

    /**
     * @var string
     */
    public $printFormat = <<<'EOT'
=================
word: %s
phonetic:
%s
translation: %s

explains:
=================
%s

internet trans:
=================
%s
EOT;

    /**
     * @var array
     */
    public $errorMaps = array(
        0 => '正常',
        20 => '要翻译的文本过长',
        30 => '无法进行有效的翻译',
        40 => '不支持的语言类型',
        50 => 'API KEY 无效',
        60 => '无词典结果， 仅在获取词典结果生效'
    );

    /**
     * Ttranslate constructor.
     */
    public function __construct($argc, $argv)
    {
        if ($argc == 1 || @$argv[2] == '-h' || @$argv[2] == '--help') {
            $this->end($this->help);
        }
        if ($argc > 1) {
            $q = implode(' ', array_slice($argv, 1));
            if ($this->isAllChinese($q)) {
                $q = urlencode($q);
            }
            $url = sprintf($this->urlFormat, "jumpaper", 254937526, $q);
            $res = file_get_contents($url);
            $resJ = json_decode($res, true);
            $phonetic = '';
            $translation = '';
            $explains = '';
            $interTrans = '';
            if (@$resJ['errorCode']) $this->end($this->errorMaps[$resJ['errorCode']], $url);
            if (@$resJ['basic']['uk-phonetic']) $phonetic .= 'uk: ' . $resJ['basic']['uk-phonetic'] . PHP_EOL;
            if (@$resJ['basic']['us-phonetic']) $phonetic .= 'us: ' . $resJ['basic']['us-phonetic'];
            if (@$resJ['translation']) $translation = implode(PHP_EOL, $resJ['translation']);
            if (@$resJ['basic']['explains']) $explains = implode(PHP_EOL, $resJ['basic']['explains']);
            if (@$resJ['web']) {
                $items = array();
                foreach ($resJ['web'] as $item):
                    $items[] = $item['key'] . ': ' . implode(',', $item['value']);
                endforeach;
                $interTrans = implode(PHP_EOL, $items);
            }
            $willPrint = sprintf($this->printFormat, $resJ['query'], $phonetic,
                $translation, $explains, $interTrans);

            echo $willPrint;
            exec('say '.$argv[1]);
        }
    }

    /**
     * @param $string
     */
    function thdump($string)
    {
        if ($this->debug)
            echo $string, PHP_EOL;
    }

    /**
     * @param $string
     * @param string $debugString
     */
    function end($string, $debugString = '')
    {
        echo $string, PHP_EOL;
        $this->thdump($debugString);
        exit;
    }

    /**
     * @param $str
     * @return bool
     */
    function isAllChinese($str)
    {
        if (strpos($str, '·')) {
            $str = str_replace("·", '', $str);
            if (preg_match('/^[\x7f-\xff]+$/', $str)) {
                return true;//全是中文
            } else {
                return false;//不全是中文
            }
        } else {
            if (preg_match('/^[\x7f-\xff]+$/', $str)) {
                return true;//全是中文
            } else {
                return false;//不全是中文
            }
        }
    }

}

/**
 * start
 */
$obj = new Translate($argc, $argv);