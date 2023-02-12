<?php

namespace app\modules\v1\controllers;


use app\filters\auth\HttpBearerAuth;

use Yii;
use yii\filters\AccessControl;
use yii\filters\auth\CompositeAuth;
use yii\rest\ActiveController;
use yii\web\HttpException;
use app\models\ReportIssue;
use app\models\Faqs;
use app\models\Coke_setting;
use app\models\Carousel_Images;
use app\models\Classification;
use app\models\CuisineTypes;
use app\models\DayWiseConfig;
use app\models\GoodFor;
use app\models\Preferences;
use app\models\States;
use app\models\User;
use app\models\UserDetails;
use app\models\LoginForm;
use app\helpers\AppHelper;
use app\models\GoogleForm;
use yii\web\UploadedFile;
use app\models\Restaurant;
use yii\db\conditions\LikeCondition;
use DateTime;
use app\models\OfferDetails;
use app\models\Restaurant_offer;
use app\models\Offer;
use app\models\PaymentModes;
use app\models\Playlists;
use app\models\PriceRange;
use app\models\OrderDelivery;
use app\models\CustomerReportIssues;

use Exception;
use app\models\RESTURANT_QR_CODES;
use app\models\Restaurant_menu_list;
use app\models\RestaurantTemporaryUploads;
use yii\helpers\Url;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
//use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use QRcode;
use PHPExcel;
use PhpOffice\PhpSpreadsheet\Reader\Xml\Style\Fill;
use yii\data\Pagination;
use yii\data\DataFilter;
use yii\data\ActiveDataProvider;
use yii\web\Response;
use yii\db\Query;
use yii\db\QueryInterface;
use yii\data\SqlDataProvider;
use yii\data\ActiveDataFilter;
use yii\base\DynamicModel;
use yii\rbac\DbManager;



class RestaurantController extends ActiveController
{
    public $modelClass = 'app\models\customer';

    public function __construct($id, $module, $config = [])
    {
        parent::__construct($id, $module, $config);
    }

    public function actions()
    {
        return [];
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => CompositeAuth::className(),
            'authMethods' => [
                HttpBearerAuth::className(),
            ],
        ];

        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::className(),
            'actions' => [
                'add-report-issue' => ['post'],
                'add-faqs' => ['post'],
                'add-coke-settings' => ['post'],
                'add-classifications' => ['post'],
                'add-carousel-images' => ['post'],
                'add-cuisine-types' => ['post'],
                'add-day-wise-config' => ['post'],
                'add-good-for' => ['post'],
                'add-preferences' => ['post'],
                'add-states' => ['post'],
                'edit-classifications' => ['post'],
                'edit-report-issue' => ['post'],
                'edit-faqs' => ['post'],
                'edit-coke-setting' => ['post'],
                'edit-carousel-images' => ['post'],
                'edit-cuisine-types' => ['post'],
                'edit-day-wise-config' => ['post'],
                'edit-good-for' => ['post'],
                'edit-preferences' => ['post'],
                'edit-states' => ['post'],
                'near-restaurant' => ['post'],
                'log-in' => ['post'],
                'read-report-issue' => ['get'],
                'read-faqs' => ['get'],
                'read-coke-settings' => ['get'],
                'read-classifications' => ['get'],
                'read-carousel-images' => ['get'],
                'read-cuisine-types' => ['get'],
                'read-day-wise-config' => ['get'],
                'read-good-for' => ['get'],
                'read-preferences' => ['get'],
                'read-states' => ['get'],
                'get-restaurant-list' => ['get'],
                'upload-restaurant-data' => ['post'],
                'upload-restaurant-offers' => ['post'],
                'upload-restaurant-qrcodes' => ['post'],
                'upload-offers' => ['post'],
                'upload-restaurant-menu-list' => ['post'],

                'update-restaurant-offers-list' => ['post'],
                'genarate-qrcode' => ['get'],
                'write-to-excel-file' => ['post'],
                'write-to-spread-sheet' => ['post'],
                'display-resturants' => ['post'],
                'resturants-pagination' => ['get'],
                'get-restaurant-offers' => ['get'],
                'get-offers' => ['get'],
                'update-offers' => ['post'],
                'update-restaurant-data' => ['post'],
                'update-restaurant-offer' => ['post'],
                'update-offers-data' => ['post'],
                'restaurant-offer-excel' => ['post'],
                'redemption-report' => ['get'],
                'restaurant-offer-report' => ['get'],
                'updatefaqs' => ['post'],
                'addrole' => ['post'],
                'update-restaurant' => ['post'],
                'update-playlist' => ['post'],
                'add-playlist' => ['post'],
                'get-price-range' => ['get'],
                'get-payment-modes' => ['get'],
                'get-order-delivery-partner' => ['get'],
                'get-customer-report-issue' => ['get'],

                'uplaod-temporary-restaurant-data' => ['post'],
                'get-temporary-restaurants' => ['get'],
                'update-temporary-restaurant' => ['post'],
                'upload-restaurant' => ['post'],
                
                'insertgoodfor'=>['post'],
                'upload-file'=>['post']

            ],
        ];

        // remove authentication filter
        $auth = $behaviors['authenticator'];
        unset($behaviors['authenticator']);

        // add CORS filter
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::className(),
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Credentials' => true,
                'Access-Control-Allow-Origin' => ['*'],
                'Access-Control-Expose-Headers' => ['X-Pagination-Page-Count', 'X-Pagination-Current-Page', 'X-Pagination-Page-Count', 'X-Pagination-Per-Page', 'X-Pagination-Total-Count'],

            ],
        ];

        // re-add authentication filter
        $behaviors['authenticator'] = $auth;
        // avoid authentication on CORS-pre-flight requests (HTTP OPTIONS method)
        $behaviors['authenticator']['except'] = [
            'add-report-issue', 'add-faqs', 'add-coke-settings', 'add-classifications', 'add-carousel-images', 'add-cuisine-types', 'add-day-wise-config', 'add-good-for', 'add-preferences',
            'add-states', 'edit-report-issue', 'edit-classifications', 'edit-faqs', 'edit-coke-setting', 'edit-carousel-images', 'edit-cuisine-types', 'edit-day-wise-config', 'edit-good-for', 'edit-preferences',
            'edit-states', 'near-restaurant', 'log-in', 'read-report-issue', 'read-faqs', 'read-coke-settings', 'read-classifications', 'read-carousel-images', 'read-cuisine-types', 'read-day-wise-config', 'read-good-for', 'read-preferences',
            'read-states',
            'upload-restaurant-data', 'upload-restaurant-offers', 'upload-restaurant-qrcodes', 'upload-offers', 'upload-restaurant-menu-list',
            'update-restaurant', 'update-offers', 'update-restaurant-offer-list', 'genarate-qrcode', 'display-resturants', 'resturants-pagination', 'get-restaurant-offers', 'get-offers', 'update-restaurant-data', 'update-offers-data', 'update-restaurant-offer', 'redemption-report', 'restaurant-offer-report', 'updatefaqs', 'addrole', 'update-playlist', 'add-playlist', 'get-price-range', 'get-payment-modes', 'get-order-delivery-partner',
            'get-customer-report-issue', 'get-restaurant-list', 'uplaod-temporary-restaurant-data',
            'get-temporary-restaurants', 'update-temporary-restaurant', 'upload-restaurant','insertgoodfor','upload-file'
        ];

        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'only' => ['index', 'view', 'create', 'update', 'delete'], //only be applied to
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['index', 'view', 'create', 'update', 'delete', 'List', 'upload-restaurant-data'],
                    'roles' => ['admin', 'manageUsers', 'hubadmin'],
                ],
                [
                    'allow' => true,
                    'actions' => ['me'],
                    'roles' => ['user'],
                ],
            ],
        ];

        return $behaviors;
    }
    public function getBearerAccessToken()
    {
        $bearer = null;
        $headers = apache_request_headers();
        if (isset($headers['Authorization'])) {
            $matches = array();
            preg_match('/^Bearer\s+(.*?)$/', $headers['Authorization'], $matches);
            if (isset($matches[1])) {
                $bearer = $matches[1];

                return $bearer;
            }
        } elseif (isset($headers['authorization'])) {
            $matches = array();
            preg_match('/^Bearer\s+(.*?)$/', $headers['authorization'], $matches);
            if (isset($matches[1])) {
                $bearer = $matches[1];
            }
        }
        return $bearer;
    }

    public function actionOptions($id = null)
    {
        return 'ok';
    }

    private function throwException($errCode, $errMsg)
    {
        throw new \yii\web\HttpException($errCode, $errMsg);
    }

    
    
    public function actionUpdateRestaurant($id)
    {
        $token = $this->getBearerAccessToken();
        if (isset($token)) {
            $appHelaper = new AppHelper();
            $checkUserRoleDetails = $appHelaper->getUserRoleDetails($token);
            if ($checkUserRoleDetails['access_token_expired_at'] > date('Y-m-d H:i:s') || $checkUserRoleDetails != NULL) {
                $model = Restaurant::find()->where(['id' => $id])->one();
                $appHelper = new AppHelper();
                $imageObj = UploadedFile::getInstance($model, 'restaurant_image');//get instance of image file using model object 

                if ($model->load(Yii::$app->request->post()) || ($imageObj)) {
                    // if(UploadedFile::getInstance($model, 'restaurant_image'))
                    if (!empty($imageObj)) {
                        $uploaded_file_url = $appHelper->addImage($imageObj);
                        $model->restaurant_image = $uploaded_file_url;
                    }
                    if ($model->save()) {
                        return "Updated  Sucessfully";
                    } else {
                       
                        return $model->getErrors(); //Display error if data bot saved/updated
                    }
                } else {
                    $this->throwException(422, "Data Not Modified.");
                }
            } else {
                $this->throwException(401, 'Unauthorized User Access');
            }
        } else {
            $this->throwException(411, 'The requested access_token could not be found');
        }
    }	
    
    public function addImage($instance)
    {

        // $instance = UploadedFile::getInstanceByName('restaurant_image');
        $file_type = $instance->extension; //get extension of file
        if ($file_type == 'jpg' || $file_type == 'jpeg') {
            $file_type = 'jpeg';
        } elseif ($file_type == 'png') {
            $file_type = 'png';
        }
        $file_name = uniqid() . '.' . $file_type; //genarate unquie name for file 
        $upload_path = 'uploads/restaurants/';
        if (!file_exists($upload_path)) {     //check wether path exit if not then create new path 
            mkdir($upload_path, 777, true);
        }
        $file_up = $upload_path.$file_name;
        $instance->saveAs($file_up);    //save file in web 
        // $url= Url::to(true).$url1.'/'.$file_name;
        // $url = Url::to('@web') . '/' . $file_up;
        $uploaded_file_url = \yii\helpers\Url::home(true) . $file_up; //genarate url 
        return $uploaded_file_url;
    }					

}


//Yii2 have model->load() property where model loads all postman data and update itself by validating 								
/* means we can update all data of table or by wrtiting perticular query based on id to and upadte that only one data .								
Requriments : 							
    1.make sure id you are passing in params of post method 						
    2.and remaing updating data as ModelName[feild_name] : preeti						
    3.it upadte only modified data , so make sure that by postman u are sending only updated data .						
    else if u sendig other feilds than its take it as null if data not present with feild.						
    4.and make sure that all your feilds declared in  rules sections, bcz only declared values here can be upadated . 						
    5. and also declare here data_type to assigin , and valiadtion if data uploading improper datatype.						
    "6.and also use model->gerError() to display why data is not saving in table , 
Example If u are updating status [integrer] as string input its shows error ."						
                            
    UPload Images Using Model->load()						
    extend  'Uploaded File ' to upload files (images , any file ) 						
    Take the Instance of the file and save in webFolder and get url link as given in  below addImage Method ()						
                            
    api url :	https://api.cokeandmeals.bigcityvoucher.co.in/v1/hubadmin/update-restaurant?id=241					
    payload : 	"Restaurant[restaurant_name]:mahesh lunch home
Restaurant[latitude]:90.888"					
    (for image uplaod)	Restaurant[restaurant_image] -> (chose any file)			select that feild as file in drop down of postman.		

    */