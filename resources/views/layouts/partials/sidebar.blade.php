<!-- Left Sidebar Start -->
<div class="app-sidebar-menu">
    <div class="h-100" data-simplebar>

        <!--- Sidemenu -->
        <div id="sidebar-menu">

            <div class="logo-box">
                <a href="{{ route('home') }}" class="logo logo-light">
                    <span class="logo-sm">
                        <img src="/images/logo-sm.png" alt="" height="22">
                    </span>
                    <span class="logo-lg">
                        <img src="/images/logo-sm.png" alt="" height="24">
                    </span>
                </a>
                <a href="{{ route('home') }}" class="logo logo-dark">
                    <span class="logo-sm">
                        <img src="/images/logo-sm.png" alt="" height="22">
                    </span>
                    <span class="logo-lg">
                        <img src="/images/logo-sm.png" alt="" height="45">
                    </span>
                </a>
            </div>

            <ul id="side-menu">

                <li class="menu-title">Menu</li>

                <li>
                    <a href="{{ route('home') }}">
                        <i data-feather="home"></i>
                        <span> Dashboard </span>
                    </a>
                    <div class="collapse" id="sidebarDashboards">
                        <ul class="nav-second-level">
                            <li>
                                <!-- <a href="{{ route('home') }}" class="tp-link">Analytical</a> -->
                            </li>
                            <!-- <li>
                                <a href="{{-- route('any', 'ecommerce') --}}" class="tp-link">E-commerce</a>
                            </li> -->
                        </ul>
                    </div>
                </li>

                <li class="menu-title">Pages</li>

                <!-- <li>
                    <a href="#sidebarAuth" data-bs-toggle="collapse">
                        <i data-feather="users"></i>
                        <span> Authentication </span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="sidebarAuth">
                        <ul class="nav-second-level">
                            <li>
                                <a class="tp-link" href="{{ route('register') }}">Log In</a>
                            </li>
                            <li>
                                <a class="tp-link" href="{{ route('register') }}">Register</a>
                            </li>
                            <li>
                                <a class="tp-link" href="{{-- route('second', ['auth', 'recoverpw']) --}}">Recover Password</a>
                            </li>
                            <li>
                                <a class="tp-link" href="{{-- route('second', ['auth', 'lockscreen']) --}}">Lock Screen</a>
                            </li>
                            <li>
                                <a class="tp-link" href="{{-- route('second', ['auth', 'confirm-mail']) --}}">Confirm Mail</a>
                            </li>
                            <li>
                                <a class="tp-link" href="{{-- route('second', ['auth', 'email-verification']) --}}">Email Verification</a>
                            </li>
                            <li>
                                <a class="tp-link" href="{{-- route('second', ['auth', 'logout']) --}}">Logout</a>
                            </li>
                        </ul>
                    </div>
                </li> -->

                <!-- <li>
                    <a href="#sidebarError" data-bs-toggle="collapse">
                        <i data-feather="alert-octagon"></i>
                        <span> Error Pages </span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="sidebarError">
                        <ul class="nav-second-level">
                            <li>
                                <a class="tp-link" href="{{-- route('second', ['error', 'error-404']) --}}">Error 404</a>
                            </li>
                            <li>
                                <a class="tp-link" href="{{-- route('second', ['error', 'error-500']) --}}">Error 500</a>
                            </li>
                            <li>
                                <a class="tp-link" href="{{-- route('second', ['error', 'error-503']) --}}">Error 503</a>
                            </li>
                            <li>
                                <a class="tp-link" href="{{-- route('second', ['error', 'error-429']) --}}">Error 429</a>
                            </li>
                            <li>
                                <a class="tp-link" href="{{-- route('second', ['error', 'offline']) --}}">Offline Page</a>
                            </li>
                        </ul>
                    </div>
                </li> -->

                <!-- <li>
                    <a href="{{ route('profile') }}">
                        <i data-feather="file-text"></i>
                        <span> Profile </span>
                    </a>
                </li> -->
                <li>
                    <a href="{{ route('sport-management.index') }}">
                        <i class="fas fa-futbol"></i> {{-- Sports icon --}}
                        <span>Sports</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('tournament-management.index') }}">
                        <i class="fas fa-trophy"></i> {{-- Tournament icon --}}
                        <span>Tournament</span>
                    </a>
                </li>

                @can('super_admin')
                <li>
                    <a href="{{ route('item-management.index') }}">
                        <i class="fas fa-cube"></i> {{-- single item --}}
                        <span>Items</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('bundle-management.index') }}">
                        <i class="fas fa-layer-group"></i> {{-- layered bundle icon --}}
                        <span>Bundles</span>
                    </a>
                </li>
                @endcan

                <li>
                    <a href="{{ route('rental-management.index') }}">
                        <i class="fas fa-handshake"></i> {{-- rental/agreement icon --}}
                        <span>Rental Management</span>
                    </a>
                </li>

                @can('super_admin')
                <li>
                    <a href="{{ route('coupon-management.index') }}">
                        <i class="fas fa-ticket-alt"></i> {{-- coupon icon --}}
                        <span>Coupon Management</span>
                    </a>
                </li>
                @endcan

                <li>
                    <a href="{{ route('chat-management.index') }}">
                        <i class="fas fa-comments"></i>
                        <span>Chat Management</span>
                    </a>
                </li>

                @can('super_admin')
                <li>
                    <a href="{{ route('faq-management.index') }}">
                        <i class="fas fa-question-circle"></i>
                        <span>FAQ Management</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('privacy-policy-management.index') }}">
                        <i class="fas fa-shield-alt"></i>
                        <span>Privacy Policy</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('terms-condition-management.index') }}">
                        <i class="fas fa-file-contract"></i>
                        <span>Terms & Conditions</span>
                    </a>
                </li>
                @endcan

                @can('super_admin')
                <li>
                    <a href="#sidebarError" data-bs-toggle="collapse" class="collapsed" aria-expanded="false">
                        <i data-feather="users"></i>
                        <span> User Management </span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="sidebarError" style="">
                        <ul class="nav-second-level">
                            <li>
                                <a class="tp-link" href="{{ route('user-management.index') }}">Users List</a>
                            </li>
                            <li>
                                <a class="tp-link" href="{{ route('role-management.index') }}">Roles</a>
                            </li>
                        </ul>
                    </div>
                </li>
                @endcan

                @can('super_admin')
                <li>
                    <a href="{{ route('booking-settings.index') }}">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </li>
                @endcan


                <!-- <li>
                    <a href="#sidebarExpages" data-bs-toggle="collapse">
                        <i data-feather="file-text"></i>
                        <span> Utility </span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="sidebarExpages">
                        <ul class="nav-second-level">
                            <li>
                                <a class="tp-link" href="{{-- route('second', ['utility', 'starter']) --}}">Starter</a>
                            </li>
                            <li>
                                <a class="tp-link" href="{{-- route('second', ['utility', 'profile']) --}}">Profile</a>
                            </li>
                            <li>
                                <a class="tp-link" href="{{-- route('second', ['utility', 'pricing']) --}}">Pricing</a>
                            </li>
                            <li>
                                <a class="tp-link" href="{{-- route('second', ['utility', 'timeline']) --}}">Timeline</a>
                            </li>
                            <li>
                                <a class="tp-link" href="{{-- route('second', ['utility', 'invoice']) --}}">Invoice</a>
                            </li>
                            <li>
                                <a class="tp-link" href="{{-- route('second', ['utility', 'faqs']) --}}">FAQs</a>
                            </li>
                            <li>
                                <a class="tp-link" href="{{-- route('second', ['utility', 'gallery']) --}}">Gallery</a>
                            </li>
                            <li>
                                <a class="tp-link" href="{{-- route('second', ['utility', 'maintenance']) --}}">Maintenance</a>
                            </li>
                            <li>
                                <a class="tp-link" href="{{-- route('second', ['utility', 'coming-soon']) --}}">Coming Soon</a>
                            </li>
                        </ul>
                    </div>
                </li> -->

                <!-- <li>
                    <a href="{{-- route('any', 'calendar') --}}" class="tp-link">
                        <i data-feather="calendar"></i>
                        <span> Calendar </span>
                    </a>
                </li> -->

                <!-- <li class="menu-title mt-2">General</li> -->

                <!-- <li>
                    <a href="#sidebarBaseui" data-bs-toggle="collapse">
                        <i data-feather="package"></i>
                        <span> Components </span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="sidebarBaseui">
                        <ul class="nav-second-level">
                            <li>
                                <a class="tp-link" href="{{-- route('second', ['components', 'accordions']) --}}">Accordions</a>
                            </li>
                            <li>
                                <a class="tp-link" href="{{-- route('second', ['components', 'alerts']) --}}">Alerts</a>
                            </li>
                            <li>
                                <a class="tp-link" href="{{-- route('second', ['components', 'badges']) --}}">Badges</a>
                            </li>
                            <li>
                                <a class="tp-link" href="{{-- route('second', ['components', 'breadcrumb']) --}}">Breadcrumb</a>
                            </li>
                            <li>
                                <a class="tp-link" href="{{-- route('second', ['components', 'buttons']) --}}">Buttons</a>
                            </li>
                            <li>
                                <a class="tp-link" href="{{-- route('second', ['components', 'cards']) --}}">Cards</a>
                            </li>
                            <li>
                                <a class="tp-link" href="{{-- route('second', ['components', 'collapse']) --}}">Collapse</a>
                            </li>
                            <li>
                                <a class="tp-link" href="{{-- route('second', ['components', 'dropdowns']) --}}">Dropdowns</a>
                            </li>
                            <li>
                                <a class="tp-link" href="{{-- route('second', ['components', 'embed-video']) --}}">Embed Video</a>
                            </li>
                            <li>
                                <a class="tp-link" href="{{-- route('second', ['components', 'grid']) --}}">Grid</a>
                            </li>
                            <li>
                                <a class="tp-link" href="{{-- route('second', ['components', 'images']) --}}">Images</a>
                            </li>
                            <li>
                                <a class="tp-link" href="{{-- route('second', ['components', 'list-group']) --}}">List Group</a>
                            </li>
                            <li>
                                <a class="tp-link" href="{{-- route('second', ['components', 'models']) --}}">Modals</a>
                            </li>
                            <li>
                                <a class="tp-link" href="{{-- route('second', ['components', 'placeholders']) --}}">Placeholders</a>
                            </li>
                            <li>
                                <a class="tp-link" href="{{-- route('second', ['components', 'pagination']) --}}">Pagination</a>
                            </li>
                            <li>
                                <a class="tp-link" href="{{-- route('second', ['components', 'popovers']) --}}">Popovers</a>
                            </li>
                            <li>
                                <a class="tp-link" href="{{-- route('second', ['components', 'progress']) --}}">Progress</a>
                            </li>
                            <li>
                                <a class="tp-link" href="{{-- route('second', ['components', 'scrollspy']) --}}">Scrollspy</a>
                            </li>
                            <li>
                                <a class="tp-link" href="{{-- route('second', ['components', 'spinners']) --}}">Spinners</a>
                            </li>
                            <li>
                                <a class="tp-link" href="{{-- route('second', ['components', 'tabs']) --}}">Tabs</a>
                            </li>
                            <li>
                                <a class="tp-link" href="{{-- route('second', ['components', 'tooltips']) --}}">Tooltips</a>
                            </li>
                            <li>
                                <a class="tp-link" href="{{-- route('second', ['components', 'typography']) --}}">Typography</a>
                            </li>
                        </ul>
                    </div>
                </li> -->

                <!-- <li>
                    <a href="{{-- route('any', 'widgets') --}}" class="tp-link">
                        <i data-feather="aperture"></i>
                        <span> Widgets </span>
                    </a>
                </li> -->

                <!-- <li>
                    <a href="#sidebarAdvancedUI" data-bs-toggle="collapse">
                        <i data-feather="cpu"></i>
                        <span> Extended UI </span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="sidebarAdvancedUI">
                        <ul class="nav-second-level">
                            <li>
                                <a class="tp-link" href="{{-- route('second', ['extended', 'carousel']) --}}">Carousel</a>
                            </li>
                            <li>
                                <a class="tp-link" href="{{-- route('second', ['extended', 'notifications']) --}}">Notifications</a>
                            </li>
                            <li>
                                <a class="tp-link" href="{{-- route('second', ['extended', 'offcanvas']) --}}">Offcanvas</a>
                            </li>
                            <li>
                                <a class="tp-link" href="{{-- route('second', ['extended', 'range-slider']) --}}">Range Slider</a>
                            </li>
                        </ul>
                    </div>
                </li> -->

                <!-- <li>
                    <a href="#sidebarIcons" data-bs-toggle="collapse">
                        <i data-feather="award"></i>
                        <span> Icons </span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="sidebarIcons">
                        <ul class="nav-second-level">
                            <li>
                                <a class="tp-link" href="{{-- route('second', ['icons', 'feather']) --}}">Feather Icons</a>
                            </li>
                            <li>
                                <a class="tp-link" href="{{-- route('second', ['icons', 'mdi']) --}}">Material Design Icons</a>
                            </li>
                        </ul>
                    </div>
                </li> -->

                <!-- <li>
                    <a href="#sidebarForms" data-bs-toggle="collapse">
                        <i data-feather="briefcase"></i>
                        <span> Forms </span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="sidebarForms">
                        <ul class="nav-second-level">
                            <li>
                                <a class="tp-link" href="{{-- route('second', ['forms', 'elements']) --}}">General Elements</a>
                            </li>
                            <li>
                                <a class="tp-link" href="{{-- route('second', ['forms', 'validation']) --}}">Validation</a>
                            </li>
                            <li>
                                <a class="tp-link" href="{{-- route('second', ['forms', 'quilljs']) --}}">Quilljs Editor</a>
                            </li>
                            <li>
                                <a class="tp-link" href="{{-- route('second', ['forms', 'pickers']) --}}">Picker</a>
                            </li>
                        </ul>
                    </div>
                </li> -->

                <!-- <li>
                    <a href="#sidebarTables" data-bs-toggle="collapse">
                        <i data-feather="table"></i>
                        <span> Tables </span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="sidebarTables">
                        <ul class="nav-second-level">
                            <li>
                                <a class="tp-link" href="{{-- route('second', ['tables', 'basic']) --}}">Basic Tables</a>
                            </li>
                            <li>
                                <a class="tp-link" href="{{-- route('second', ['tables', 'datatables']) --}}">Data Tables</a>
                            </li>
                        </ul>
                    </div>
                </li> -->

                <!-- <li>
                    <a href="#sidebarCharts" data-bs-toggle="collapse">
                        <i data-feather="pie-chart"></i>
                        <span> Apex Charts </span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="sidebarCharts">
                        <ul class="nav-second-level">
                            <li>
                                <a class="tp-link" class="tp-link" href="{{-- route('second', ['charts', 'line']) --}}">Line</a>
                            </li>
                            <li>
                                <a class="tp-link" class="tp-link" href="{{-- route('second', ['charts', 'area']) --}}">Area</a>
                            </li>
                            <li>
                                <a class="tp-link" class="tp-link" href="{{-- route('second', ['charts', 'column']) --}}">Column</a>
                            </li>
                            <li>
                                <a class="tp-link" class="tp-link" href="{{-- route('second', ['charts', 'bar']) --}}">Bar</a>
                            </li>
                            <li>
                                <a class="tp-link" class="tp-link" href="{{-- route('second', ['charts', 'mixed']) --}}">Mixed</a>
                            </li>
                            <li>
                                <a class="tp-link" class="tp-link" href="{{-- route('second', ['charts', 'timeline']) --}}">Timeline</a>
                            </li>
                            <li>
                                <a class="tp-link" class="tp-link" href="{{-- route('second', ['charts', 'rangearea']) --}}">Range Area</a>
                            </li>
                            <li>
                                <a class="tp-link" class="tp-link" href="{{-- route('second', ['charts', 'funnel']) --}}">Funnel</a>
                            </li>
                            <li>
                                <a class="tp-link" class="tp-link" href="{{-- route('second', ['charts', 'candlestick']) --}}">Candlestick</a>
                            </li>
                            <li>
                                <a class="tp-link" class="tp-link" href="{{-- route('second', ['charts', 'boxplot']) --}}">Boxplot</a>
                            </li>
                            <li>
                                <a class="tp-link" class="tp-link" href="{{-- route('second', ['charts', 'bubble']) --}}">Bubble</a>
                            </li>
                            <li>
                                <a class="tp-link" class="tp-link" href="{{-- route('second', ['charts', 'scatter']) --}}">Scatter</a>
                            </li>
                            <li>
                                <a class="tp-link" class="tp-link" href="{{-- route('second', ['charts', 'heatmap']) --}}">Heatmap</a>
                            </li>
                            <li>
                                <a class="tp-link" class="tp-link" href="{{-- route('second', ['charts', 'treemap']) --}}">Treemap</a>
                            </li>
                            <li>
                                <a class="tp-link" class="tp-link" href="{{-- route('second', ['charts', 'pie']) --}}">Pie</a>
                            </li>
                            <li>
                                <a class="tp-link" class="tp-link" href="{{-- route('second', ['charts', 'radialbar']) --}}">Radialbar</a>
                            </li>
                            <li>
                                <a class="tp-link" class="tp-link" href="{{-- route('second', ['charts', 'radar']) --}}">Radar</a>
                            </li>
                            <li>
                                <a class="tp-link" class="tp-link" href="{{-- route('second', ['charts', 'polararea']) --}}">Polar</a>
                            </li>
                        </ul>
                    </div>
                </li> -->

                <!-- <li>
                    <a href="#sidebarMaps" data-bs-toggle="collapse">
                        <i data-feather="map"></i>
                        <span> Maps </span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="sidebarMaps">
                        <ul class="nav-second-level">
                            <li>
                                <a class="tp-link" href="{{-- route('second', ['maps', 'googlemap']) --}}" class="tp-link">Google Maps</a>
                            </li>
                            <li>
                                <a class="tp-link" href="{{-- route('second', ['maps', 'vectormap']) --}}" class="tp-link">Vector Maps</a>
                            </li>
                        </ul>
                    </div>
                </li> -->

            </ul>

        </div>
        <!-- End Sidebar -->

        <div class="clearfix"></div>

    </div>
</div>
<!-- Left Sidebar End -->
