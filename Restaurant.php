<?php

namespace app\models;

use app\helpers\AppHelper;
use yii\data\Pagination;
use yii\data\ActiveDataProvider;
use yii\data\SqlDataProvider;
use Yii;

class Restaurant extends \yii\db\ActiveRecord
{

  /**
   * @inheritdoc
   */

  const ROLE_HUBADMIN = 3;
  const ROLE_REPORTADMIN = 6;


  public static function tableName()
  {
    return 'restaurants';
  }

  /** @inheritdoc */
  public function rules()
  {
    return [
      [['classification_ids'], 'safe'],
      [['status', 'id', 'country_id', 'pincode', 'state_id'], 'integer'],
      [['created_date', 'updated_date', 'offers', 'restaurant_image'], 'safe'],
      [['restaurant_name', 'restaurant_code', 'restaurant_tags','cuisine_type_id'], 'string'],
      [['latitude', 'longitude', 'restaurant_image', 'address', 'city', 'mobile_no', 'website_url', 'restaurant_content', 'description'], 'string'],
      [['good_for_id', 'price_range', 'twitter_link', 'fb_link', 'insta_link', 'google_place_id', 'partner_name'], 'string'],
      [['mon_open', 'mon_close', 'tue_open', 'tue_close', 'wed_open', 'wed_close', 'thu_open', 'thu_close', 'fri_open', 'fri_close', 'sat_open', 'sat_close', 'sun_open', 'sun_close', 'updated_date', 'created_date'], 'safe'],
    //  [['price_range_id'],'string'],
      [['price_range_id'],'integer'], //price_range_id is integer bcz , each resturant have only one price_range
      
      [['order_delivery_ids','payment_mode_ids','preferences_ids'],'string'],

    //  [['order_delivery_ids'],'ValidateResturantsFeilds'],
    ];
  }

  public function attributeLabels()
  {
    return [
      // 'id' => 'id',
      'restaurant_name' =>  'restaurant_name',
      'restaurant_code' => 'restaurant_code',
      'latitude' => 'latitude',
      'longitude' =>  'longitude',
      'restaurant_image' => 'restaurant_image',
      'restaurant_tags' => 'restaurant_tags',
      'address' =>  'address',
      'city' =>  'city',
      'country_id' => 'country_id',
      'pincode' => 'pincode',
      'state_id' => 'state_id',
      'mobile_no' =>  'mobile_no',
      'website_url' => 'website_url',
      'classification_ids' => 'classification_ids',
      'restaurant_content' =>  'restaurant_content',
      'description' =>  'description',
      'mon_open' => 'mon_open',
      'mon_close' => 'mon_close',
      'tue_open' =>  'tue_open',
      'tue_close' => 'tue_close',
      'wed_open' => 'wed_open',
      'wed_close' =>  'wed_close',
      'thu_open' =>  'thu_open',
      'thu_close' => 'thu_close',
      'fri_open' => 'fri_open',
      'fri_close' =>  'fri_close',
      'sat_open' => 'sat_open',
      'sat_close' => 'sat_close',
      'sun_open' =>  'sun_open',
      'thu_open' =>  'thu_open',
      'thu_close' => 'thu_close',
      'fri_open' => 'fri_open',
      'fri_close' =>  'fri_close',
      'sat_open' => 'sat_open',
      'sat_close' => 'sat_close',
      'sun_open' =>  'sun_open',
      'status' => 'status',
      'updated_date' => 'updated_date',
      'created_date' => 'created_date',
      'cuisine_type_id' => 'cuisine_type_id',
      'good_for_id' => 'good_for_id',
      'fb_link' => 'fb_link',
      'insta_link' => 'insta_link',
      'twitter_link' => 'twitter_link',
      'price_range' => 'price_range',
      'price_range_id'=>'price_range_id',
      'google_place_id' => 'google_place_id',
      'play_list' => 'play_list',
      'order_delivery_ids' => 'order_delivery_ids'

    ];
  }

  public function ValidateResturantsFeilds($attribute)
    {
        //validation for taking input values number strings 1,2,3... for order_delivery_ids/payment_mode_ids/cuisine_type_id/ preferences_ids
        $value = '[0-9]+';
        $match = "~^$value(,$value)*$~i";
        $ids=$this->attribute;   
        if (preg_match($match, $ids)) {
            return $ids;
        } else {
            $this->throwException(422, 'Input order_delivery_ids/payment_mode_ids/cuisine_type_id/ preferences_ids Are Not Proper ');
        }
    }
