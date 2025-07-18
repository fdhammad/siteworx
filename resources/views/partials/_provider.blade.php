<div class="page-title-wrap d-flex justify-content-between gap-3 flex-wrap mb-3 p-3">
    <h2 class="page-title">{{__('messages.provider_detail')}}</h2>
    </div>


<div class="mb-3">
    <ul class="nav nav-tabs pay-tabs nav--tabs__style2 provider-detail-tab">
        <li class="nav-item {{request()->routeIs('provider_info') ? 'active' : ''}}">
            <a class="nav-link" href="{{ route('provider_info',$providerdata->id) }}"> {{__('messages.overview')}}</a>
        </li>
        @if (default_earning_type() === 'subscription')
        <li class="nav-item {{request()->routeIs('service.show') ? 'active' : ''}}">
                <a class="nav-link" href="{{ route('service.show',$providerdata->id) }}"> {{__('messages.plan')}}</a> 
        </li>
        @endif
        <li class="nav-item {{request()->routeIs('booking.details') ? 'active' : ''}}">
            <a class="nav-link" href="{{ route('booking.details',$providerdata->id) }}"> {{__('messages.Bookings')}}</a>
        </li>
        <li class="nav-item {{request()->routeIs('handyman.show') ? 'active' : ''}}">
            <a class="nav-link" href="{{ route('handyman.show',$providerdata->id) }}">{{__('messages.handyman')}}</a>
        </li>
        <li class="nav-item {{request()->routeIs('setting.comission') ? 'active' : ''}}">
            <a class="nav-link" href="{{ route('setting.comission',$providerdata->id) }}">{{__('messages.commission')}}</a>
        </li>
        <!-- <li class="nav-item {{request()->routeIs('bank.show') ? 'active' : ''}}">
            <a class="nav-link" href="{{ route('bank.show',$providerdata->id) }}">{{__('messages.Bank_info')}}</a>
        </li> -->
        <li class="nav-item {{request()->routeIs('provider.review') ? 'active' : ''}}">
            <a class="nav-link" href="{{ route('provider.review',$providerdata->id) }}">{{__('messages.Reviews')}}</a>
        </li>
        <li class="nav-item {{request()->routeIs('providerdocument.show') ? 'active' : ''}}">
            <a class="nav-link" href="{{ route('providerdocument.show',$providerdata->id) }}">{{__('messages.list_form_title',['form' => __('messages.document')])}}</a>
        </li>
        <li class="nav-item {{request()->routeIs('providerpayout.show') ? 'active' : ''}}">
            <a class="nav-link" href="{{ route('providerpayout.show',$providerdata->id) }}">{{__('messages.list_form_title',['form' => __('messages.provider_payout')])}}</a>
        </li>
        <!-- <li class="nav-item {{request()->routeIs('provideraddress.show') ? 'active' : ''}}">
            <a class="nav-link" href="{{ route('provideraddress.show',$providerdata->id) }}">{{__('messages.list_form_title',['form' => __('messages.provider_address')])}}</a>
        </li> -->
        @if ($auth_user->can('bank list'))
        <li class="nav-item {{request()->routeIs('bank.list') ? 'active' : ''}}">
            <a class="nav-link" href="{{ route('bank.list',$providerdata->id) }}">{{__('messages.list_form_title',['form' => __('messages.bank')])}}</a>
        </li>
        @endif
        <li class="nav-item {{request()->routeIs('provider.time-slot') ? 'active' : ''}}">
            <a class="nav-link" href="{{ route('provider.time-slot',$providerdata->id) }}">{{__('messages.list_form_title',['form' => __('messages.manage_slot')])}}</a>
        </li>
    </ul>
</div>
