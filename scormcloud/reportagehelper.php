<?php
class ReportageHelper
{

    private $_appId = null;

    public function __construct($appId)
    {
        $this->_appId = $appId;
    }

    public function GetWidgetUrl($auth, $widgettype, $widgetSettings = null)
    {
        $rServiceUrl = "https://cloud.scorm.com";

        switch ($widgettype) {
            case 'allSummary':
                $reporturl = $rServiceUrl . '/Reportage/scormreports/widgets/summary/SummaryWidget.php';
                $reporturl .= '?srt=allLearnersAllCourses';
                break;
            case 'courseSummary':
                $reporturl = $rServiceUrl . '/Reportage/scormreports/widgets/summary/SummaryWidget.php';
                $reporturl .= '?srt=singleCourse';
                break;
            case 'learnerSummary':
                $reporturl = $rServiceUrl . '/Reportage/scormreports/widgets/summary/SummaryWidget.php';
                $reporturl .= '?srt=singleLearner';
                break;
            case 'learnerCourseSummary':
                $reporturl = $rServiceUrl . 'Reportage/scormreports/widgets/summary/SummaryWidget.php';
                $reporturl .= '?srt=singleLearnerSingleCourse';
                break;
            case 'courseActivities':
            case 'learnerRegistration':
            case 'courseComments':
            case 'learnerComments':
            case 'courseInteractions':
            case 'learnerInteractions':
            case 'learnerActivities':
            case 'courseRegistration':
            case 'learnerRegistration':
            case 'learnerCourseActivities':
            case 'learnerTranscript':
            case 'learnerCourseInteractions':
            case 'learnerCourseComments':

                $reporturl = $rServiceUrl . '/Reportage/scormreports/widgets/DetailsWidget.php';
                $reporturl .= '?drt=' . $widgettype;
                break;
            case 'allLearners':
                $reporturl = $rServiceUrl . '/Reportage/scormreports/widgets/ViewAllDetailsWidget.php';
                $reporturl .= '?viewall=learners';
                break;
            case 'allCourses':
                $reporturl = $rServiceUrl . '/Reportage/scormreports/widgets/ViewAllDetailsWidget.php';
                $reporturl .= '?viewall=courses';
                break;
            default:
                break;
        }

        $reporturl .= '&appId=' . $this->_appId;

        //process the WidgetSettings
        if (isset($widgetSettings)) {
            if ($widgetSettings->hasCourseId()) {
                $reporturl .= '&courseId=' . $widgetSettings->getCourseId();
            }
            if ($widgetSettings->hasLearnerId()) {
                $reporturl .= '&learnerId=' . $widgetSettings->getLearnerId();
            }

            $reporturl .= '&showTitle=' . boolString($widgetSettings->getShowTitle());
            $reporturl .= '&standalone=' . boolString($widgetSettings->getStandalone());
            if ($widgetSettings->getIframe()) {
                $reporturl .= '&iframe=true'; //only write this if it's true
            }
            $reporturl .= '&expand=' . boolString($widgetSettings->getExpand());
            $reporturl .= '&scriptBased=' . boolString($widgetSettings->getScriptBased());
            $reporturl .= '&divname=' . $widgetSettings->getDivname();
            $reporturl .= '&vertical=' . boolString($widgetSettings->getVertical());
            $reporturl .= '&embedded=' . boolString($widgetSettings->getEmbedded());
            //$reporturl .= '&viewall='.boolString($widgetSettings->getViewAll());
            //$reporturl .= '&export='.boolString($widgetSettings->getExport());

            //Process the DateRangeSettings
            $dateRangeSettings = $widgetSettings->getDateRangeSettings();
            if (isset($dateRangeSettings)) {

                switch ($dateRangeSettings->getDateRangeType()) {
                    case 'selection': //daterange
                        $reporturl .= '&dateRangeType=c';
                        $reporturl .= '&dateRangeStart=' . $dateRangeSettings->getDateRangeStart();
                        $reporturl .= '&dateRangeEnd=' . $dateRangeSettings->getDateRangeEnd();
                        $reporturl .= '&dateCriteria=' . $dateRangeSettings->getDateCriteria();
                        break;
                    case 'mtd': //month to date
                        $reporturl .= '&dateRangeType=mtd';
                        $reporturl .= '&dateCriteria=' . $dateRangeSettings->getDateCriteria();
                        break;
                    case 'ytd': //year to date
                        $reporturl .= '&dateRangeType=ytd';
                        $reporturl .= '&dateCriteria=' . $dateRangeSettings->getDateCriteria();
                        break;
                    //case 'p3m': ??
                    default:
                        break;
                }
            }

            //Process Tags
            $tagSettings = $widgetSettings->getTagSettings();
            if (isset($tagSettings)) {
                $tagSettings = $widgetSettings->getTagSettings();
                if ($tagSettings->hasTags('learner')) {
                    $reporturl .= '&learnerTags=' . $tagSettings->getTagString('learner');
                    $reporturl .= '&viewLearnerTagGroups=' . $tagSettings->getViewTagString('learner');
                }
                if ($tagSettings->hasTags('course')) {
                    $reporturl .= '&courseTags=' . $tagSettings->getTagString('course');
                    $reporturl .= '&viewCourseTagGroups=' . $tagSettings->getViewTagString('course');
                }
                if ($tagSettings->hasTags('registration')) {
                    $reporturl .= '&registrationTags=' . $tagSettings->getTagString('registration');
                    $reporturl .= '&viewRegistrationTagGroups=' . $tagSettings->getViewTagString('registration');
                }

            }

            //Process Comparisons
            $comparisonSettings = $widgetSettings->getComparisonSettings();
            if (isset($comparisonSettings)) {

                $reporturl .= '&compDateRangeType=ad';
                $reporturl .= '&compDateCriteria=launched';
                $reporturl .= '&compLearnerTags=_all';
                $reporturl .= '&compDateRangeType=ad';
                $reporturl .= '&compDateCriteria=launched';
                $reporturl .= '&compViewLearnerTagGroups=_all';
                $reporturl .= '&compViewCourseTagGroups=_all';
                $reporturl .= '&compViewRegistrationTagGroups=_all';
                $reporturl .= '&compMatchDates=1';
            }
        }
        //error_log("ReportUrl: ".$reporturl);
        return $reporturl;
    }

    public function GetReportageDate()
    {
        $rServiceUrl = "https://cloud.scorm.com";
        $reportageUrl = $rServiceUrl . 'Reportage/scormreports/api/getReportDate.php?appId=' . $this->_appId;
        return $this->submitHttpPost($reportageUrl);
    }

    /**
     * Number of seconds to wait while connecting to the server.
     */
    const TIMEOUT_CONNECTION = 30;
    /**
     * Total number of seconds to wait for a request.
     */
    const TIMEOUT_TOTAL = 500;

    public static function submitHttpPost($url, $postParams = null, $timeout = self::TIMEOUT_TOTAL, $curlProxy = null)
    {
        //foreach($postParams as $key => $value)
        //{
        //    echo $key.'='.$value.'<br>';
        //}
        $ch = curl_init();

        // set up the request
        curl_setopt($ch, CURLOPT_URL, $url);
        // make sure we submit this as a post
        curl_setopt($ch, CURLOPT_POST, true);
        if (isset($postParams)) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postParams);
        } else {
            curl_setopt($ch, CURLOPT_POSTFIELDS, "");
        }
        // make sure problems are caught
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        // return the output
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // set the timeouts
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::TIMEOUT_CONNECTION);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

        //set header expect empty for upload issue...
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));

        //set proxy
        if (!empty($curlProxy)) {
            curl_setopt($ch, CURLOPT_PROXY, $curlProxy);
        }

        // set the PHP script's timeout to be greater than CURL's
        set_time_limit(self::TIMEOUT_CONNECTION + $timeout + 5);

        $result = curl_exec($ch);
        // check for errors
        if (0 == curl_errno($ch)) {
            curl_close($ch);
            return $result;
        } else {
            //$ex = new Phlickr_ConnectionException(
            //echo 'Request failed. '.curl_error($ch);
            curl_close($ch);
            //throw $ex;
        }
    }

}

class WidgetSettings
{

    private $_dateRangeSettings;
    private $_tagSettings;
    private $_comparisonSettings;

    private $_courseid = null;
    private $_learnerid = null;

    private $_showTitle = true;
    private $_vertical = false;
    private $_public = true;
    private $_standalone = true;
    private $_iframe = false;
    private $_expand = true;
    private $_scriptBased = true;
    private $_divname = '';
    private $_pubNavPermission = 'DOWNONLY';
    private $_embedded = true;
    private $_viewall = true;
    private $_export = true;

    public function __construct($dateRangeSettings = null, $tagSettings = null, $comparisonSettings = null)
    {
        $this->_dateRangeSettings = $dateRangeSettings;
        $this->_tagSettings = $tagSettings;
        $this->_comparisonSettings = $comparisonSettings;
    }

    public function getDateRangeSettings()
    {
        return $this->_dateRangeSettings;
    }
    public function getTagSettings()
    {
        return $this->_tagSettings;
    }
    public function getComparisonSettings()
    {
        return $this->_comparisonSettings;
    }

    public function setCourseId($val)
    {
        $this->_courseid = $val;
    }
    public function getCourseId()
    {
        return $this->_courseid;
    }
    public function hasCourseId()
    {
        return isset($this->_courseid);
    }

    public function setLearnerId($val)
    {
        $this->_learnerid = $val;
    }
    public function getLearnerId()
    {
        return $this->_learnerid;
    }
    public function hasLearnerId()
    {
        return isset($this->_learnerid);
    }

    public function setShowTitle($val)
    {
        $this->_showTitle = $val;
    }
    public function getShowTitle()
    {
        return $this->_showTitle;
    }
    public function setVertical($val)
    {
        $this->_vertical = $val;
    }
    public function getVertical()
    {
        return $this->_vertical;
    }
    public function setPublic($val)
    {
        $this->_public = $val;
    }
    public function getPublic()
    {
        return $this->_public;
    }
    public function setStandalone($val)
    {
        $this->_standalone = $val;
    }
    public function getStandalone()
    {
        return $this->_standalone;
    }
    public function setIframe($val)
    {
        $this->_iframe = $val;
    }
    public function getIframe()
    {
        return $this->_iframe;
    }
    public function setExpand($val)
    {
        $this->_expand = $val;
    }
    public function getExpand()
    {
        return $this->_expand;
    }
    public function setScriptBased($val)
    {
        $this->_scriptBased = $val;
    }
    public function getScriptBased()
    {
        return $this->_scriptBased;
    }
    public function setDivname($val)
    {
        $this->_divname = $val;
    }
    public function getDivname()
    {
        return $this->_divname;
    }
    public function setPubNavPermission($val)
    {
        $this->_pubNavPermission = $val;
    }
    public function getPubNavPermission()
    {
        return $this->_pubNavPermission;
    }
    public function setEmbedded($val)
    {
        $this->_embedded = $val;
    }
    public function getEmbedded()
    {
        return $this->_embedded;
    }
    public function setViewAll($val)
    {
        $this->_viewall = $val;
    }
    public function getViewAll()
    {
        return $this->_viewall;
    }
    public function setExport($val)
    {
        $this->_export = $val;
    }
    public function getExport()
    {
        return $this->_export;
    }
}

class DateRangeSettings
{

    private $_dateRangeType = null;
    private $_dateRangeStart = null;
    private $_dateRangeEnd = null;
    private $_dateCriteria = null;

    public function __construct($dateRangeType, $dateRangeStart, $dateRangeEnd, $dateCriteria)
    {
        $this->_dateRangeType = $dateRangeType;
        $this->_dateRangeStart = $dateRangeStart;
        $this->_dateRangeEnd = $dateRangeEnd;
        $this->_dateCriteria = $dateCriteria;
    }

    public function getDateRangeType()
    {
        return $this->_dateRangeType;
    }
    public function getDateRangeStart()
    {
        return $this->_dateRangeStart;
    }
    public function getDateRangeEnd()
    {
        return $this->_dateRangeEnd;
    }
    public function getDateCriteria()
    {
        return $this->_dateCriteria;
    }
}

class TagSettings
{

    private $_tags = array('learner' => array(), 'course' => array(), 'registration' => array());

    public function addTag($tagType, $newValue)
    {
        $this->_tags[$tagType][] = $newValue;
    }

    public function getTagString($tagType)
    {
        return implode(",", $this->_tags[$tagType]) . "|_all";
    }

    public function getViewTagString($tagType)
    {
        return implode(",", $this->_tags[$tagType]);
    }

    public function hasTags($tagType)
    {
        return (count($this->_tags[$tagType]) > 0);
    }
}

class ComparisonSettings
{
    public $compMatchDates = '1';
    public $compDateRangeType = 'ad';
    public $compDateCriteria = 'launched';
    public $compLearnerTags = '_all';
    public $compViewLearnerTagGroups = '_all';
    public $compViewCourseTagGroups = '_all';
    public $compViewRegistrationTagGroups = '_all';
}

function boolString($bValue = false)
{
// returns string
    return ($bValue ? 'true' : 'false');
}
