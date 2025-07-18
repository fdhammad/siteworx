<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wallet;
use App\Models\WalletHistory;
use Yajra\DataTables\DataTables;
use App\Traits\NotificationTrait;
use App\Models\Setting;
use App\Models\Country;
use App\Models\Payment;
use App\Models\WithdrawMoney;
use Carbon\Carbon;
use App\Models\User;

class WalletController extends Controller
{
    use NotificationTrait;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $filter = [
            'status' => $request->status,
        ];
        $pageTitle = __('messages.list_form_title',['form' => __('messages.wallet')] );
        $auth_user = authSession();
        $assets = ['datatable'];
        return view('wallet.index', compact('pageTitle','auth_user','assets','filter'));
    }


    public function index_data(DataTables $datatable,Request $request)
    {
        $query = Wallet::query()->list();
        $filter = $request->filter;
        $query = $query->orderBy('updated_at','desc');
        if (isset($filter)) {
            if (isset($filter['column_status'])) {
                $query->where('status', $filter['column_status']);
            }
        }
        if (auth()->user()->hasAnyRole(['admin'])) {
            $query->newquery();
        }

        return $datatable->eloquent($query)
            ->addColumn('check', function ($row) {
                return '<input type="checkbox" class="form-check-input select-table-row"  id="datatable-row-'.$row->id.'"  name="datatable_ids[]" value="'.$row->id.'" onclick="dataTableRowCheck('.$row->id.')">';
            })
            ->editColumn('title', function($query){
                return '<a class="btn-link btn-link-hover" href='.route('wallet.show', $query->user_id).'>'.$query->title.'</a>';
            })

            ->editColumn('user_id' , function ($query){
                return view('wallet.user', compact('query'));
            })
            ->editColumn('amount' , function ($query){
                return getPriceFormat($query->amount);
            })
            ->editColumn('status' , function ($query){
                return '<div class="custom-control custom-switch custom-switch-text custom-switch-color custom-control-inline">
                    <div class="custom-switch-inner">
                        <input type="checkbox" class="custom-control-input  change_status" data-type="wallet_status" '.($query->status ? "checked" : "").'  value="'.$query->id.'" id="'.$query->id.'" data-id="'.$query->id.'">
                        <label class="custom-control-label" for="'.$query->id.'" data-on-label="" data-off-label=""></label>
                    </div>
                </div>';
            })
            ->addColumn('action', function($wallet){
                return view('wallet.action',compact('wallet'))->render();
            })
            ->addIndexColumn()
            ->rawColumns(['title','action','status','check'])
            ->toJson();
    }

    /* bulck action method */
    public function bulk_action(Request $request)
    {
        $ids = explode(',', $request->rowIds);

        $actionType = $request->action_type;

        $message = 'Bulk Action Updated';

        switch ($actionType) {
            case 'change-status':
                $branches = Wallet::whereIn('id', $ids)->update(['status' => $request->status]);
                $message = 'Bulk Wallet Status Updated';
                break;

            case 'delete':
                Wallet::whereIn('id', $ids)->delete();
                $message = 'Bulk Wallet Deleted';
                break;

            default:
                return response()->json(['status' => false, 'message' => 'Action Invalid']);
                break;
        }

        return response()->json(['status' => true, 'message' => $message]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $id = $request->id;
        $auth_user = authSession();

        $wallet = Wallet::find($id);
        $pageTitle = trans('messages.update_form_title',['form'=>trans('messages.wallet')]);

        if($wallet == null){
            $pageTitle = trans('messages.add_button_form',['form' => trans('messages.wallet')]);
            $wallet = new Wallet;
        }

        return view('wallet.create', compact('pageTitle' ,'wallet' ,'auth_user' ));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(demoUserPermission()){
            return  redirect()->back()->withErrors(trans('messages.demo_permission_denied'));
        }
        $data = $request->all();
        $data['user_id'] = !empty($request->user_id) ? $request->user_id : auth()->user()->id;
        $wallet = Wallet::where('user_id',$data['user_id'])->first();
        if($wallet && !$data['id']){
            $message = __('messages.already_wallet');
            return redirect()->back()->withError($message);
        }
        if($wallet !== null){
            $data['amount'] = $wallet->amount + $request->amount;
        }
        $result = Wallet::updateOrCreate(['id' => $data['id'] ],$data);


        $message = trans('messages.update_form',['form' => trans('messages.wallet')]);
        if($result->wasRecentlyCreated){
            $activity_data = [
                'activity_type' => 'add_wallet',
                'wallet' => $result,
            ];
            $this->sendNotification($activity_data);

            $message = trans('messages.save_form',['form' => trans('messages.wallet')]);
        }else{
            if($wallet->amount  != $data['amount']){
                $activity_data = [
                    'activity_type' => 'update_wallet',
                    'wallet' => $result,
                    'added_amount' =>$request->amount
                ];
                $this->sendNotification($activity_data);

            }
        }
        if($request->is('api/*')) {
            return comman_message_response($message);
		}
        return redirect(route('wallet.index'))->withSuccess($message);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user=User::where('id',$id)->first();
        $username=$user->display_name ?? null;
        $pageTitle = __('messages.wallet_history',['user_name' =>$username] );
        $auth_user = authSession();
        $assets = ['datatable'];
        return view('wallet.view', compact('pageTitle','auth_user','assets','id'));
    }

    public function wallethistory_index_data(DataTables $datatable,$id){
        $query = WalletHistory::where('user_id',$id)->orderBy('id','desc')->newQuery();

        if (auth()->user()->hasAnyRole(['admin'])) {
            $query->newquery();
        }

        return $datatable ->eloquent($query)
        ->editColumn('user_id' , function ($history){
            return ($history->user_id != null && isset($history->providers)) ? $history->providers->display_name : '-';
        })
        ->editColumn('activity_type' , function ($history){
            return $history->activity_type ? str_replace("_"," ",ucfirst($history->activity_type)) : '-';
        })
        ->addColumn('amount', function ($history) {
            $activityData = json_decode($history->activity_data, true);
            return isset($activityData['credit_debit_amount']) ? getPriceFormat($activityData['credit_debit_amount']) : 0;
        })
        ->addIndexColumn()
        ->toJson();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if(demoUserPermission()){
            return  redirect()->back()->withErrors(trans('messages.demo_permission_denied'));
        }
        $wallet = Wallet::find($id);
        $msg= __('messages.msg_fail_to_delete',['item' => __('messages.wallet')] );

        if($wallet != '') {
            $wallet->delete();
            $msg= __('messages.wallet_deleted');
        }
        return comman_custom_response(['message'=> $msg, 'status' => true]);
    }

    public function getWalletPaymentMethod(Request $request)
    {
        $data = $request->all();
        $data['datetime'] = now();
        $data['payment_status'] = 'failed';
        $payment_data = Payment::where('booking_id', $data['booking_id'])->first();

        if (!empty($payment_data)) {
            $payment_data->update($data);
        } else {
            $payment_data = Payment::create($data);
        }

        $sitesetup = Setting::where('type','site-setup')->where('key', 'site-setup')->first();
        $sitesetupdata = $sitesetup ? json_decode($sitesetup->value, true) : null;
        $country_id = $sitesetupdata['default_currency'] ?? null;
        $country = Country::find($country_id);

        $data['currency_code'] = $country ? $country->currency_code : "USD";

        switch ($data['payment_type']) {
            case 'stripe':
                $data['payment_geteway_data'] = getPaymentMethodkey($data['payment_type']);
                break;

            default:

                break;
        }

        return comman_custom_response($data);
    }
    public function createWalletStripePayment(Request $request)
    {

        $data = $request->all();

        $checkout_session = addWalletAmount($data);
        if(isset($checkout_session['message'])) {

            return comman_custom_response($checkout_session);

        }else{
            Payment::where('booking_id', $data['booking_id'])->update(['other_transaction_detail' => $checkout_session['id']]);

            return comman_custom_response($checkout_session);
       }

    }
    public function saveWalletStripePayment(Request $request, $id){


        $request->validate([
            'amount' => 'required|integer',
        ]);

        $user_id = $id ?? auth()->user()->id;

        $wallet = Wallet::where('user_id', $user_id)->first();

        if($wallet){

            $wallet->amount += $request->amount;
            $wallet->save();

            $activity_data = [

                'activity_type' => 'wallet_top_up',
                'wallet' => $wallet,
                'top_up_amount' =>$wallet->amount,
                'transaction_type'=> $wallet->transcation_type,
                'transaction_id'=> $wallet->transcation_id,

            ];
            $this->sendNotification($activity_data);

            return redirect('/booking-list');

          }

     }

    public function wallet_transaction_index(Request $request)
    {
        $filter = [
            'status' => $request->status,
        ];
        $pageTitle = __('messages.list_form_title',['form' => __('messages.provider_withdrawal_requests')] );
        $auth_user = authSession();
        $assets = ['datatable'];
        return view('wallet.transaction_index', compact('pageTitle','auth_user','assets','filter'));
    }

    public function wallet_transaction_index_data(DataTables $datatable,Request $request)
    {
        $query = WithdrawMoney::query()->with('providers','bank');

        if(auth()->user()->hasRole('provider')){
            $query = $query->where('user_id', auth()->user()->id);
        }

        $filter = $request->filter;
        $query = $query->orderBy('updated_at','desc');
        if (isset($filter)) {
            if (isset($filter['column_status'])) {
                $query->where('status', $filter['column_status']);
            }
        }
        if (auth()->user()->hasAnyRole(['admin'])) {
            $query->newquery();
        }

        return $datatable->eloquent($query)
            ->addColumn('check', function ($row) {
                return '<input type="checkbox" class="form-check-input select-table-row"  id="datatable-row-'.$row->id.'"  name="datatable_ids[]" value="'.$row->id.'" onclick="dataTableRowCheck('.$row->id.')">';
            })
            ->editColumn('user_id' , function ($query){
                return view('wallet.user', compact('query'));
            })

            ->filterColumn('user_id',function($query,$keyword){
                $query->whereHas('providers',function ($q) use($keyword){
                    $q->where('display_name','like','%'.$keyword.'%');
                });
            })
            ->orderColumn('user_id', function ($query, $order) {
                $query->select('withdraw_money.*')
                      ->join('users as customers', 'customers.id', '=', 'withdraw_money.user_id')
                      ->orderBy('customers.display_name', $order);   
            })
            ->editColumn('bank_id', function($query) {
                $bankName = '';
                if($query->payment_type == 'manual'){
                    $bankName = __('messages.manual_transaction');
                }
                else{
                    $bankName = optional($query->bank)->bank_name;
                    $accountNo = optional($query->bank)->account_no;
                    if ($accountNo) {
                        $maskedAccountNo = str_repeat('X', strlen($accountNo) - 4) . substr($accountNo, -4);
                        return '<b>' . $bankName.'</b>'  . '<br>' . $maskedAccountNo . '</br>';
                    }
                }
                return $bankName;
            })
            ->orderColumn('bank_id', function ($query, $order) {
                $query->select('withdraw_money.*')
                      ->join('banks', 'banks.id', '=', 'withdraw_money.bank_id')
                      ->orderBy('banks.bank_name', $order);   
            })
            
            ->editColumn('amount' , function ($query){
                return getPriceFormat($query->amount);
            })
            ->editColumn('payment_type' , function ($query){
                return ucfirst($query->payment_type);
            })
            ->editColumn('user_type' , function ($query){
                return optional($query->providers)->user_type ?? '-';
            })
            ->filterColumn('user_type',function($query,$keyword){
                $query->whereHas('providers',function ($q) use($keyword){
                    $q->where('user_type','like','%'.$keyword.'%');
                });
            })
            ->editColumn('datetime' , function ($query){
                $sitesetup = Setting::where('type','site-setup')->where('key', 'site-setup')->first();
                $datetime = $sitesetup ? json_decode($sitesetup->value) : null;

                $date = optional($datetime)->date_format && optional($datetime)->time_format
                ? date(optional($datetime)->date_format, strtotime($query->datetime)) .'  '. date(optional($datetime)->time_format, strtotime($query->datetime))
                : $query->datetime;

                return $date;

            })
            ->editColumn('status' , function ($query){
                if($query->status == 'paid'){
                    $status = '<span class="badge badge-active text-success bg-success-subtle">'.ucfirst($query->status).'</span>';
                }else{
                    $status = '<span class="badge badge-inactive text-danger bg-danger-subtle">'.ucfirst($query->status).'</span>';
                }
                return $status;
            })
            ->addColumn('action', function($query){
                $allData = WithdrawMoney::all();
                $exists = $allData->contains('withdraw_money_id', $query->id);
                return view('wallet.transaction_action',compact('query','exists'))->render();
                
            })
            ->addIndexColumn()
            ->rawColumns(['user_id','bank_id','amount','datetime','action','status','check'])
            ->toJson();
    }

    public function wallet_transaction_payout($id){
        $withdraw_money = WithdrawMoney::where('id', $id)->first();

        $wallet = Wallet::where('user_id', $withdraw_money->user_id)->first();

        $wallet->amount -= $withdraw_money->amount;
        $wallet->save();

        $new_withdraw_money = new WithdrawMoney();
        $new_withdraw_money->user_id = $withdraw_money->user_id;
        $new_withdraw_money->bank_id = $withdraw_money->bank_id;
        $new_withdraw_money->amount = $withdraw_money->amount;
        $new_withdraw_money->datetime = Carbon::now();
        $new_withdraw_money->payment_type = 'manual';
        $new_withdraw_money->withdraw_money_id = $withdraw_money->id;
        $new_withdraw_money->status = 'paid';
        $new_withdraw_money->save();

        $message= __('messages.transaction_complete_success');
        return redirect(route('wallet_transaction'))->withSuccess($message);
    }
}
