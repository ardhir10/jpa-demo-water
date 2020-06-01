 
<div class="br-sideleft sideleft-scrollbar bg-white shadow tx-black-5 {{(!isset($monitor)) ?: 'hilang'}}">
    <ul class="br-sideleft-menu">

        {{-- <label class="sidebar-label pd-x-10 mg-t-20 op-3">Device </label>
        <li class="br-menu-item device-status">
            <a href="javascript:void(0);" class="br-menu-link rounded-10 "   data-toggle="modal" data-target="#selectDevice">
                <i class="menu-item-icon fa fa-microchip tx-23"></i>
                <span class="menu-item-label">MODBUS 1
                    
                </span>
                
            </a><!-- br-menu-link -->
        </li><!-- br-menu-item --> --}}
        <label class="sidebar-label pd-x-10 mg-t-20 op-3">Navigation</label>

        <li class="br-menu-item">
            <a href="{{url('dashboard')}}" class="br-menu-link rounded-10">
                <i class="menu-item-icon icon ion-ios-home-outline tx-24"></i>
                <span class="menu-item-label">Dashboard</span>
            </a><!-- br-menu-link -->
        </li><!-- br-menu-item -->

        <li class="br-menu-item">
            <a href="{{url('trending/report')}}" class="br-menu-link rounded-10">
                <i class="menu-item-icon icon ion-arrow-graph-up-right tx-24"></i>
                <span class="menu-item-label">Trending Report</span>
            </a><!-- br-menu-link -->
        </li><!-- br-menu-item -->
        <li class="br-menu-item">
            <a href="{{url('alarm/alarm-list')}}" class="br-menu-link rounded-10">
                                <i class="menu-item-icon icon ion-ios-alarm-outline tx-20"></i>

                <span class="menu-item-label">Alarm</span>
            </a><!-- br-menu-link -->
        </li><!-- br-menu-item -->

       
        <li class="br-menu-item">
            <a href="{{url('api/logs')}}" class="br-menu-link rounded-10">
                <i class="menu-item-icon icon ion-ios-cloud-upload-outline tx-24"></i>
                <span class="menu-item-label">Api Logs</span>
            </a><!-- br-menu-link -->
        </li><!-- br-menu-item -->







        {{-- <label class="sidebar-label pd-x-10 mg-t-20 op-3">master </label> --}}


       
        {{-- <label class="sidebar-label pd-x-10 mg-t-20 op-3">Setting</label> --}}
        <li class="br-menu-item">
            <a href="{{url('settings')}}" class="br-menu-link rounded-10">
                <i class="menu-item-icon ion-ios-cog-outline tx-24"></i>
                <span class="menu-item-label">Settings</span>
            </a><!-- br-menu-link -->
        </li><!-- br-menu-item -->
        <li class="br-menu-item">
            <a href="{{ route('logout') }}" class="br-menu-link rounded-10" onclick=" event.preventDefault(); 
                    var r = confirm('Are you sure want Logout?');
                if (r == true) {
                 document.getElementById('logout-form').submit();
                } else {
                 return false
                } 
                 "><i class="menu-item-icon icon ion-power"></i>
                <span class="menu-item-label">Logout</span></a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        </li><!-- br-menu-item -->

    </ul><!-- br-sideleft-menu -->

    {{-- <label class="sidebar-label pd-x-10 mg-t-25 mg-b-20 tx-info">Information Summary</label> --}}

    
    <br>
</div><!-- br-sideleft -->
