<!DOCTYPE html>
<html lang="ar" dir="rtl" direction="rtl" style="direction: rtl;">

<head>
    <base href="" />
    <title>{{ $brand->name }}</title>
    <meta charset="utf-8" />
    <meta name="user-id" content="{{ auth()->id() }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="vapid-public-key" content="{{ config('webpush.vapid.public_key') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    <!--begin::Fonts-->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Cairo:300,400,500,600,700" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!--end::Fonts-->

    <!--begin::Global Stylesheets Bundle(mandatory for all pages)-->
    <link href="{{ asset('assets/plugins/global/plugins.bundle.rtl.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/style.bundle.rtl.css') }}" rel="stylesheet" type="text/css" />
    <!--end::Global Stylesheets Bundle-->
    @include('layouts._pwa_head')

    <!--begin::Fontawesome-->
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200"
        rel="stylesheet" />
    <!--end::Fontawesome-->
<script>
    window.userId = {{ auth()->id() }};
</script>

    <style>
        body {
            font-family: 'Cairo', 'Inter', sans-serif !important;
        }

        /* Card Hover Effects */
        .hover-lift {
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .hover-lift:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15) !important;
        }

        /* Filter Button Active State */




        /* Form Control Focus */
        .form-control:focus,
        .form-select:focus {
            border-color: var(--bs-primary);
            box-shadow: 0 0 0 0.25rem rgba(var(--bs-primary-rgb), 0.15);
        }

        /* Smooth transitions */
        .card {
            transition: all 0.2s ease-in-out;
        }
    </style>
</head>

<body id="kt_body" class="header-fixed header-tablet-and-mobile-fixed aside-enabled aside-fixed @auth has-bottom-nav @endauth" style="--kt-toolbar-height:55px;--kt-toolbar-height-tablet-and-mobile:55px">

    <div class="d-flex flex-column flex-root">
        <div class="page d-flex flex-row flex-column-fluid">

            @auth
            <div id="kt_aside" class="aside aside-dark aside-hoverable"
                 data-kt-drawer="true" data-kt-drawer-name="aside"
                 data-kt-drawer-activate="{default: true, lg: false}"
                 data-kt-drawer-overlay="true"
                 data-kt-drawer-width="{default:'200px', '250px': '300px'}"
                 data-kt-drawer-direction="start"
                 data-kt-drawer-toggle="#kt_aside_mobile_toggle">



                <div class="aside-menu flex-column-fluid">
                    <div class="hover-scroll-overlay-y my-5" data-kt-scroll="true" data-kt-scroll-height="auto" data-kt-scroll-offset="0">
                        <div class="menu menu-column menu-title-gray-300 menu-state-title-primary" id="#kt_aside_menu" data-kt-menu="true">
                            @include('layouts._menu_links')
                        </div>
                    </div>
                </div>
            </div>
            @endauth

            <div class="wrapper d-flex flex-column flex-row-fluid" id="kt_wrapper">

                <div id="kt_header" class="header align-items-stretch py-lg-3 border-bottom bg-white px-4 px-lg-10">
                    <div class="container-fluid d-flex align-items-stretch justify-content-between">

                        <div class="d-flex align-items-center d-lg-none ms-n3 me-1">
                            <div class="btn btn-icon btn-active-light-primary w-35px h-35px" id="kt_aside_mobile_toggle">
                                <i class="bi bi-list fs-1"></i>
                            </div>
                        </div>

                        <div class="d-flex align-items-stretch flex-grow-1">


                            <div class="d-none d-lg-flex align-items-stretch" id="kt_header_nav">
                                <div class="header-menu align-items-stretch d-flex">
                                    <div class="menu menu-lg-row menu-state-bg menu-title-gray-700 menu-state-title-primary fw-bold my-5 my-lg-0 align-items-stretch" data-kt-menu="true">
                                        @include('layouts._menu_links')
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex align-items-stretch flex-shrink-0 my-3 my-lg-0">

                            <div class="d-none d-xxl-flex align-items-center gap-2 gap-lg-3 bg-light-primary p-2 px-3 px-lg-4 rounded-3 border border-primary border-opacity-10 shadow-sm ms-2 ms-lg-4">
                                <div class="text-end">
                                    <div id="clock" class="fs-6 fs-lg-4 fw-bolder lh-1 text-primary font-monospace"></div>
                                    <div id="date" class="fs-8 fw-bold text-gray-600 mt-1"></div>
                                </div>
                            </div>

                            @auth
                            <div class="d-flex align-items-center ms-2">
                                <div class="btn btn-icon btn-light-primary position-relative w-35px h-35px" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                    <i class="bi bi-bell fs-4"></i>
                                    <span id='notification-count' class="position-absolute top-0 start-0 badge badge-circle badge-danger w-15px h-15px {{ auth()->user()->unreadNotifications->count() > 0 ? '' : 'd-none' }}">
                                        {{ auth()->user()->unreadNotifications->count() }}
                                    </span>
                                </div>
                                <div class="menu menu-sub menu-sub-dropdown menu-column w-350px w-lg-375px" data-kt-menu="true">

                                        <div class="d-flex flex-column bgi-no-repeat rounded-top bg-primary">

                                            <div class="d-flex align-items-center justify-content-between px-9 mt-10 mb-6 gap-3">
                                                <h3 class="text-white fw-bold mb-0">الإشعارات
                                                    <span class="fs-8 opacity-75 ps-3" id="notification-subtext">لا توجد إشعارات جديدة</span>
                                                </h3>
                                                <button type="button" id="notification-sound-toggle" class="btn btn-sm btn-light-primary d-flex align-items-center gap-2 px-3 py-2">
                                                    <i id="notification-sound-toggle-icon" class="bi bi-volume-up-fill fs-6"></i>
                                                    <span id="notification-sound-toggle-text">كتم الصوت</span>
                                                </button>
                                            </div>

                                        </div>                                    <div class="scroll-y mh-325px my-5 px-8" id="notification-items-list">
                                    @forelse(auth()->user()->notifications as $notification)
                                        @php
                                            $ticketId = $notification->data['ticket_id'] ?? null;
                                            $isAdmin = auth()->user()->role->name === 'Admin';
                                            $path = $isAdmin ? 'admin/tickets/' : 'tickets/';
                                            $url = $ticketId ? url($path . $ticketId) : url($path);
                                        @endphp
                                        <a href="{{ $url }}" class="p-3 border-bottom {{ $notification->read_at ? '' : 'bg-light-primary' }} notification-item d-block text-decoration-none">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                            </div>
                                            <p class="mb-0 text-dark text-end fs-7 lh-base">
                                                {{ $notification->data['message'] ?? 'إشعار جديد' }}
                                            </p>
                                        </a>
                                    @empty
                                        <div class="text-muted text-center w-100 py-10">
                                            لا توجد تنبيهات حالياً
                                        </div>
                                    @endforelse
                                </div>
                                </div>
                            </div>

                            <div class="d-flex align-items-center ms-3">
                                <a href="{{ route('profile.index') }}" class="d-flex align-items-center text-dark text-hover-primary">
                                    <span class="d-none d-md-inline fw-bold me-2">{{ Auth::user()->fullname }}</span>
                                    <i class="bi bi-person-circle fs-2 text-primary"></i>
                                </a>
                            </div>

                            <div class="ms-3 d-flex align-items-center">
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button class="btn btn-sm btn-light-danger px-3">خروج</button>
                                </form>
                            </div>
                            @endauth
                        </div>
                    </div>
                </div>

                <div class="content d-flex flex-column flex-column-fluid py-10">
                    <div class="container-xxl">
                        @yield('content')
                    </div>
                </div>

               <div class="footer py-12 bg-light border-top d-none d-lg-block" id="kt_footer">
                <div class="container-xxl">
                    <div class="row g-10">
                        <div class="col-lg-4">
                            <div class="d-flex align-items-center mb-5">
                                <img alt="" src="{{ $brand->logoUrl() }}" class="h-40px me-3 rounded" />
                                <div>
                                    <span class="text-gray-900 fw-bolder fs-3">{{ $brand->name }}</span>
                                    <div class="text-muted text-end fw-bold fs-7">
                                        {{ date('Y') }} © {{ $brand->organization ?: $brand->name }}
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
            </div>
        </div>
    </div>


    @auth
        @include('layouts._bottom_nav')
    @endauth



<div class="modal fade" id="user_details_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 shadow border-0">
            <div class="modal-header border-0 pb-0 my-3 text-center d-block">
                <h5 class="modal-title fw-bolder fs-4">بيانات صاحب التذكرة</h5>
            </div>
            <div class="modal-body py-5 text-end">
                <div class="d-flex flex-column gap-4">
                    <div class="p-3 bg-light rounded-3">
                        <div class="text-muted small fw-bold">الاسم الكامل</div>
                        <div class="fw-bolder text-dark" id="modal_user_fullname">---</div>
                    </div>
                    <div class="p-3 bg-light rounded-3">
                        <div class="text-muted small fw-bold">البريد الإلكتروني</div>
                        <div class="fw-bolder text-dark" id="modal_user_email">---</div>
                    </div>
                    <div class="p-3 bg-light rounded-3">
                        <div class="text-muted small fw-bold">مكان العمل</div>
                        <div class="fw-bolder text-dark" id="modal_user_office_location">---</div>
                    </div>
                    <div class="p-3 bg-light rounded-3">
                        <div class="text-muted small fw-bold">الفروع</div>
                        <div class="fw-bolder text-dark" id="modal_user_branches">---</div>
                    </div>
                    <div class="p-3 bg-light rounded-3">
                        <div class="text-muted small fw-bold">رقم الجوال / واتساب</div>
                        <div class="fw-bolder text-dark" id="modal_user_whatsapp_number">---</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light fw-bold" data-bs-dismiss="modal">إغلاق</button>
            </div>
        </div>
    </div>
</div>

    <!--begin::Javascript-->
    <script src="{{ asset('assets/plugins/global/plugins.bundle.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script src="{{ asset('assets/js/scripts.bundle.js') }}"></script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
   <script>
    window.isAdmin = {{ auth()->user()->role->name === 'Admin' ? 'true' : 'false' }};
    window.userId = "{{ auth()->id() }}";





     @if(session('success'))

            Swal.fire({
                icon: 'success',
                title: 'نجاح!',
                text: @json(session('success')),
                showConfirmButton: false,
                timer: 2000,
                customClass: {
                    popup: 'border-radius-15'
                }
            });

        @endif

        @if(session('info'))
            Swal.fire({
                icon: 'info',
                title: 'تنبيه',
                text: @json(session('info')),
                showConfirmButton: true,
                confirmButtonText: 'فهمت',
                customClass: {
                    popup: 'border-radius-15',
                    confirmButton: 'btn btn-info'
                }

            });

        @endif

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = String(text);
        return div.innerHTML;
    }

    document.addEventListener("DOMContentLoaded", function() {
        const countBadge = document.getElementById('notification-count');
        const list = document.getElementById('notification-items-list');
        const notificationBtn = document.querySelector('[data-kt-menu-trigger="click"]');
        const notificationSoundToggle = document.getElementById('notification-sound-toggle');
        const notificationSoundToggleIcon = document.getElementById('notification-sound-toggle-icon');
        const notificationSoundToggleText = document.getElementById('notification-sound-toggle-text');
        const notificationSoundStorageKey = `notification_sound_muted_${window.userId}`;
        let isNotificationSoundMuted = localStorage.getItem(notificationSoundStorageKey) === 'true';

        function renderNotificationSoundState() {
            if (!notificationSoundToggle || !notificationSoundToggleIcon || !notificationSoundToggleText) {
                return;
            }

            if (isNotificationSoundMuted) {
                notificationSoundToggleIcon.className = 'bi bi-volume-mute-fill fs-6';
                notificationSoundToggleText.textContent = 'تشغيل الصوت';
                notificationSoundToggle.classList.remove('btn-light-primary');
                notificationSoundToggle.classList.add('btn-light-danger');
            } else {
                notificationSoundToggleIcon.className = 'bi bi-volume-up-fill fs-6';
                notificationSoundToggleText.textContent = 'كتم الصوت';
                notificationSoundToggle.classList.remove('btn-light-danger');
                notificationSoundToggle.classList.add('btn-light-primary');
            }
        }

        renderNotificationSoundState();

        if (notificationSoundToggle) {
            notificationSoundToggle.addEventListener('click', function(event) {
                event.preventDefault();
                event.stopPropagation();

                isNotificationSoundMuted = !isNotificationSoundMuted;
                localStorage.setItem(notificationSoundStorageKey, String(isNotificationSoundMuted));
                renderNotificationSoundState();
            });
        }

        if (notificationBtn) {
            notificationBtn.addEventListener('click', function() {
                if (countBadge && !countBadge.classList.contains('d-none')) {
                    countBadge.classList.add('d-none');
                    countBadge.innerText = '0';

                    fetch("{{ route('notifications.markRead') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': "{{ csrf_token() }}",
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    }).catch(err => console.error("Error marking as read:", err));
                }
            });
        }

        if (window.Echo) {
            window.Echo.private(`App.Models.User.${window.userId}`)
                .notification((notification) => {
                    if (countBadge) {
                        let currentCount = parseInt(countBadge.innerText) || 0;
                        countBadge.innerText = currentCount + 1;
                        countBadge.classList.remove('d-none');
                    }

                    const noMsg = document.getElementById('no-notifications-msg');
                    if (noMsg) noMsg.style.display = 'none';

                    const t_id = notification.ticket_id;
                    const routePrefix = window.isAdmin ? '/admin/tickets/' : '/tickets/';

                    const ticketUrl = t_id
                        ? (window.location.origin + routePrefix + t_id)
                        : (window.location.origin + routePrefix);

                    const newItem = `
                        <a href="${ticketUrl}" class="p-3 border-bottom bg-light-primary notification-item d-block text-decoration-none animate__animated animate__fadeIn">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <small class="text-muted">الآن</small>
                            </div>
                            <p class="mb-0 text-dark text-end fs-7 lh-base">${escapeHtml(notification.message || 'إشعار جديد')}</p>
                        </a>
                    `;
                    if (list) list.insertAdjacentHTML('afterbegin', newItem);

                    if (!isNotificationSoundMuted) {
                        const audio = new Audio('/assets/media/notification/notification.mp3');
                        audio.play().catch(e => console.warn("Audio blocked"));
                    }
                });
        }
    });

    $(document).ready(function() {
        @auth
            @php
                $hasFinished = auth()->user()->tickets()->where('status', 'finished')->exists();
                $targetRoute = route('dashboard', ['status' => 'finished']);
            @endphp
            let hasFinishedTicket = @json($hasFinished);
            let finishedTicketUrl = @json($targetRoute);
        @else
            let hasFinishedTicket = false;
        @endauth

        $('.btn-create-ticket').on('click', function(e) {
            if(hasFinishedTicket) {
                e.preventDefault();
               Swal.fire({
                    title: 'تنبيه!',
                    text: "لديك تذاكر منتهية، يرجى مراجعتها قبل إنشاء تذكرة جديدة.",
                    icon: 'warning',
                    iconColor: @json($brand->primaryColor),
                    showCancelButton: true,
                    buttonsStyling: false,
                    confirmButtonText: 'عرض التذاكر المنتهية',
                    cancelButtonText: 'إلغاء',
                    customClass: {
                        popup: 'border-radius-15',
                        confirmButton: 'btn btn-primary',
                        cancelButton: 'btn btn-light'
                    }

                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = finishedTicketUrl;
                    }
                });
            }

        });

        $(document).on('click', '.view-user-details', function(e) {
            e.preventDefault();
            const userId = $(this).data('user-id');
            const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('user_details_modal'));
            $('#user_details_modal .fw-bolder.text-dark').text('جاري التحميل...');
            modal.show();
            $.get(`/admin/users/${userId}/details`, function(response) {
                $('#modal_user_fullname').text(response.full_name || '---');
                $('#modal_user_email').text(response.email || '---');
                $('#modal_user_whatsapp_number').text(response.whatsapp_number || '---');
                $('#modal_user_office_location').text(response.office_location || '---');
                $('#modal_user_branches').text(response.branches || 'كل الفروع');
            });
        });
    });
    </script>

    @stack('scripts')
</body>

</html>
