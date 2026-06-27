<?php

/*
 | --------------------------------------------------------------------
 | App Namespace
 | --------------------------------------------------------------------
 |
 | This defines the default Namespace that is used throughout
 | CodeIgniter to refer to the Application directory. Change
 | this constant to change the namespace that all application
 | classes should use.
 |
 | NOTE: changing this will require manually modifying the
 | existing namespaces of App\* namespaced-classes.
 */
defined('APP_NAMESPACE') || define('APP_NAMESPACE', 'App');

/*
 | --------------------------------------------------------------------------
 | Composer Path
 | --------------------------------------------------------------------------
 |
 | The path that Composer's autoload file is expected to live. By default,
 | the vendor folder is in the Root directory, but you can customize that here.
 */
defined('COMPOSER_PATH') || define('COMPOSER_PATH', ROOTPATH . 'vendor/autoload.php');

/*
 |--------------------------------------------------------------------------
 | Timing Constants
 |--------------------------------------------------------------------------
 |
 | Provide simple ways to work with the myriad of PHP functions that
 | require information to be in seconds.
 */
defined('SECOND') || define('SECOND', 1);
defined('MINUTE') || define('MINUTE', 60);
defined('HOUR')   || define('HOUR', 3600);
defined('DAY')    || define('DAY', 86400);
defined('WEEK')   || define('WEEK', 604800);
defined('MONTH')  || define('MONTH', 2_592_000);
defined('YEAR')   || define('YEAR', 31_536_000);
defined('DECADE') || define('DECADE', 315_360_000);

/*
 | --------------------------------------------------------------------------
 | Exit Status Codes
 | --------------------------------------------------------------------------
 |
 | Used to indicate the conditions under which the script is exit()ing.
 | While there is no universal standard for error codes, there are some
 | broad conventions.  Three such conventions are mentioned below, for
 | those who wish to make use of them.  The CodeIgniter defaults were
 | chosen for the least overlap with these conventions, while still
 | leaving room for others to be defined in future versions and user
 | applications.
 |
 | The three main conventions used for determining exit status codes
 | are as follows:
 |
 |    Standard C/C++ Library (stdlibc):
 |       http://www.gnu.org/software/libc/manual/html_node/Exit-Status.html
 |       (This link also contains other GNU-specific conventions)
 |    BSD sysexits.h:
 |       http://www.gsp.com/cgi-bin/man.cgi?section=3&topic=sysexits
 |    Bash scripting:
 |       http://tldp.org/LDP/abs/html/exitcodes.html
 |
 */
defined('EXIT_SUCCESS')        || define('EXIT_SUCCESS', 0);        // no errors
defined('EXIT_ERROR')          || define('EXIT_ERROR', 1);          // generic error
defined('EXIT_CONFIG')         || define('EXIT_CONFIG', 3);         // configuration error
defined('EXIT_UNKNOWN_FILE')   || define('EXIT_UNKNOWN_FILE', 4);   // file not found
defined('EXIT_UNKNOWN_CLASS')  || define('EXIT_UNKNOWN_CLASS', 5);  // unknown class
defined('EXIT_UNKNOWN_METHOD') || define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
defined('EXIT_USER_INPUT')     || define('EXIT_USER_INPUT', 7);     // invalid user input
defined('EXIT_DATABASE')       || define('EXIT_DATABASE', 8);       // database error
defined('EXIT__AUTO_MIN')      || define('EXIT__AUTO_MIN', 9);      // lowest automatically-assigned error code
defined('EXIT__AUTO_MAX')      || define('EXIT__AUTO_MAX', 125);    // highest automatically-assigned error code

define('MSG_CLOSING_PERIODE','Periode transaksi sudah closing');
define('DIR_TICKET_RESULT','dokumen/upload/ticket/results/');
define('DIR_TICKET_REPLIES','dokumen/upload/ticket_replies/');
define('DIR_TICKET_NILAI','dokumen/upload/ticket/nilai_customer/');
define('DIR_TICKET','dokumen/upload/ticket/');
define('DIR_WO_RESULT','dokumen/upload/work_order/result/');
define('DIR_WO_NOTES','dokumen/upload/work_order/notes/');

define('ID_DEPT_TR',10);
define('ID_DEPT_ENGI','11');
define('ID_DEPT_BM','7');
define('ID_DEPT_FIN_BM','12');
define('ID_JAB_MGR','3');


define('HOME_DEPT_TR','tr_page');
define('HOME_DEPT_ENGI','engineer_page');
define('HOME_DEPT_FIN_BM','fin_bm');

define('ID_JAB_CHIEF','6');
define('ID_JAB_SPV','7');

define('ID_STS_TENANT_LAINNYA','7');

define('ID_TIPEWO_RC','3');
define('ID_TIPEWO_WO','1');
define('ID_TIPEWO_WARRANTY','2');

define('NO_WHATSAPP_TR','6281219985057');

//status ticket config
define('ID_STS_TICKET_CLOSED',6);
define('ID_STS_TICKET_SURVEY_INPUT','3');
define('ID_STS_TICKET_PROGRESS','3');
define('ID_STS_TICKET_SCHEDULED','2');
define('ID_STS_TICKET_OPEN','1');
define('ID_STS_TICKET_SOLVED','4');
define('ID_STS_TICKET_PROGRESS_APPROVED','5');

//status WO config
define('ID_STS_WO_OPEN','1');
define('ID_STS_WO_CONFIRMATION','2');
define('ID_STS_WO_GEN_INVOICE','3');
define('ID_STS_WO_PAID','5');
define('ID_STS_WO_UNPAID','4');
define('ID_STS_WO_SCHEDULED','6');
define('ID_STS_WO_PROGRESS','7');
define('ID_STS_WO_END','8');
define('ID_STS_WO_CLOSED',9);

//wo config directory
define('ID_SERVICE_WO','27');
define('DIR_WO_COMMENT','dokumen/upload/work_order/comment/');
// define('LINK_QR_WEBSEC','http://192.168.50.21/websec/assets/qrcode/');
// define('LINK_QR_WEBSEC','http://141.136.47.112:404/assets/qrcode/');
define('LINK_QR_WEBSEC','http://websec.stuservice.id/public/qrcode/');

define('URL_IPI_SSO','https://sso.indopasifik.co.id/');
define('URL_IPI_SSO_FORGOT','https://sso.indopasifik.co.id/forgot_password');
define('URL_WEBSEC', 'http://apiwebsec.stuservice.id/');
define('ID_APPS', 9);
