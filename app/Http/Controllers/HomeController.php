<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Weidner\Goutte\GoutteFacade;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('home');
    }

    public function scraping(Request $request)
    {
        try {
            $goutte = GoutteFacade::request('GET', $request->input('eatlogurl'));

            $score = $goutte->filter('.rdheader-rating__score-val-dtl')->text();
            if ($score) {
                $shopinfos = array();
                $goutte->filter('.c-table tr')->each(function ($node) use (&$shopinfos) {
                    $value = $node->filter('td')->text();
                    $value = str_replace(array(" ", "　"), "", $value);
                    if (strpos($value, '地図') !== false) {
                        $value = str_replace("大きな地図を見る", "", $value);
                        $value = str_replace("周辺のお店を探す", "", $value);
                    }
                    $shopinfos[] = $value;
                });
                $goutte->filter('.homepage')->each(function ($node) use (&$homepage) {
                    $homepage = $node->filter('.c-link-arrow')->text();
                    $homepage = trim($homepage);
                });
                $img = $goutte->filter('.rstdtl-top-postphoto__list')->first()->filter('img')->attr('src');

                foreach (config('const.EATLOGS.INDEX_LIST') as $key => $value) {
                    if (isset($shopinfos[$value])) {
                        $shopinfos[$key] = $shopinfos[$value];
                    } else {
                        $shopinfos[$key] = '';
                    }
                }
            }
        } catch (\Exception $e) {
            session()->flash('msg_danger', config('const.MESSAGE_DATA_GET_ERROR'));
            return view('home');
        }

        $eatlogdata = array(
            'eatlogurl'        => array('info' => $request->input('eatlogurl'), 'label' => '食べログサイト'),
            'score'            => array('info' => $score, 'label' => config('const.SCORE')),
            'shopname'         => array('info' => $shopinfos['Shopname'], 'label' => config('const.EATLOGS.NAME_LIST.Shopname')),
            'reserve_tel'      => array('info' => $shopinfos['Reserve_tel'], 'label' => config('const.EATLOGS.NAME_LIST.Reserve_tel')),
            'reserve_judgment' => array('info' => $shopinfos['Reserve_judgment'], 'label' => config('const.EATLOGS.NAME_LIST.Reserve_judgment')),
            'address'          => array('info' => $shopinfos['Address'], 'label' => config('const.EATLOGS.NAME_LIST.Address')),
            'business_hours'   => array('info' => $shopinfos['Business_hours'], 'label' => config('const.EATLOGS.NAME_LIST.Business_hours')),
            'payment_method'   => array('info' => $shopinfos['Payment_method'], 'label' => config('const.EATLOGS.NAME_LIST.Payment_method')),
            'service_charge'   => array('info' => $shopinfos['Service_charge'], 'label' => config('const.EATLOGS.NAME_LIST.Service_charge')),
            'private_room'     => array('info' => $shopinfos['Private_room'], 'label' => config('const.EATLOGS.NAME_LIST.Private_room')),
            'smoking_judgment' => array('info' => $shopinfos['Smoking_judgment'], 'label' => config('const.EATLOGS.NAME_LIST.Smoking_judgment')),
            'parking'          => array('info' => $shopinfos['Parking'], 'label' => config('const.EATLOGS.NAME_LIST.Parking')),
            'hp'               => array('info' => $homepage, 'label' => config('const.EATLOGS.NAME_LIST.Hp')),
            'shoptel'          => array('info' => $shopinfos['Shoptel'], 'label' => config('const.EATLOGS.NAME_LIST.Shoptel')),
            'img'              => array('info' => $img)
        );

        session()->flash('msg_success', config('const.MESSAGE_DATA_GET_SUCCESS'));

        return view('home', compact('eatlogdata'));
    }
}
