<div class="row">
    <div class="col-lg-12">
        <ul class="nav nav-tabs pay-tabs nav-fill tabslink" id="tab-text" role="tablist">
            <li class="nav-item payment-link">
                <a href="javascript:void(0)" data-href="{{ route('landing_layout_page') }}?tabpage=section_1" data-target=".payment_paste_here" data-toggle="tabajax"  class="nav-link  {{$tabpage=='section_1'?'active':''}}"   rel="tooltip"> {{ __('messages.home_slider') }}</a>
            </li>
            <li class="nav-item payment-link">
                <a href="javascript:void(0)" data-href="{{ route('landing_layout_page') }}?tabpage=section_2" data-target=".payment_paste_here" data-toggle="tabajax"  class="nav-link  {{$tabpage=='section_2'?'active':''}}"   rel="tooltip"> {{__('messages.popular_categories')}}</a>
            </li>
            <li class="nav-item payment-link">
                <a href="javascript:void(0)" data-href="{{ route('landing_layout_page') }}?tabpage=section_3" data-target=".payment_paste_here" data-toggle="tabajax"  class="nav-link  {{$tabpage=='section_3'?'active':''}}"   rel="tooltip"> {{__('messages.top_rated_services')}}</a>
            </li>
            <li class="nav-item payment-link">
                <a href="javascript:void(0)" data-href="{{ route('landing_layout_page') }}?tabpage=section_4" data-target=".payment_paste_here" data-toggle="tabajax"  class="nav-link  {{$tabpage=='section_4'?'active':''}}"   rel="tooltip"> {{__('messages.featured_services')}}</a>
            </li>
            <li class="nav-item payment-link">
                <a href="javascript:void(0)" data-href="{{ route('landing_layout_page') }}?tabpage=section_8" data-target=".payment_paste_here" data-toggle="tabajax"  class="nav-link  {{$tabpage=='section_8'?'active':''}}"   rel="tooltip"> {{__('messages.recently_viewed_services')}}</a>
            </li>
            <li class="nav-item payment-link">
                <a href="javascript:void(0)" data-href="{{ route('landing_layout_page') }}?tabpage=section_5" data-target=".payment_paste_here" data-toggle="tabajax"  class="nav-link  {{$tabpage=='section_5'?'active':''}}"   rel="tooltip"> {{__('messages.join_us')}}</a>
            </li>
            <li class="nav-item payment-link">
                <a href="javascript:void(0)" data-href="{{ route('landing_layout_page') }}?tabpage=section_9" data-target=".payment_paste_here" data-toggle="tabajax"  class="nav-link  {{$tabpage=='section_9'?'active':''}}"   rel="tooltip"> {{__('messages.our_clients')}}</a>
            </li>
            <li class="nav-item payment-link">
                <a href="javascript:void(0)" data-href="{{ route('landing_layout_page') }}?tabpage=section_6" data-target=".payment_paste_here" data-toggle="tabajax"  class="nav-link  {{$tabpage=='section_6'?'active':''}}"   rel="tooltip"> {{__('messages.discover_our_app')}}</a>
            </li>
            <li class="nav-item payment-link">
                <a href="javascript:void(0)" data-href="{{ route('landing_layout_page') }}?tabpage=section_7" data-target=".payment_paste_here" data-toggle="tabajax"  class="nav-link  {{$tabpage=='section_7'?'active':''}}"   rel="tooltip"> {{__('messages.how_it_works')}}</a>
            </li>
        </ul>
        <div class="card payment-content-wrapper">
            <div class="card-body">
                <div class="tab-content" id="pills-tabContent-1">
                    <div class="tab-pane active p-1" >
                        <div class="payment_paste_here"></div>


                    
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>  
<script>
    $(document).ready(function(event)
    {
        // Get the tabpage from the URL
        const urlParams = new URLSearchParams(window.location.search);
        const tabpage = urlParams.get('tabpage') || 'section_1'; // Default to section_1 if not set

        // Set the active tab based on the tabpage
        $('.payment-link a[data-href*="tabpage=' + tabpage + '"]').addClass('active');
        
        // Load the content for the active tab
        loadurl = '{{ route('landing_layout_page') }}?tabpage=' + tabpage;

        $.post(loadurl, { '_token': $('meta[name=csrf-token]').attr('content') }, function(data) {
            $('.payment_paste_here').html(data);
        });

        $this.tab('show');
        return false;
    });
</script>