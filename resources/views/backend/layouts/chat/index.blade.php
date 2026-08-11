@extends('backend.app')
@push('styles')
<style>
    /* Chat Custom Styles */
    .main-chat-list .media {
        padding: 15px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 8px;
        margin-bottom: 5px;
        transition: all 0.3s ease;
        background-color: #23262f !important;
        text-decoration: none;
        display: block;
        color: #f1f1f1 !important;
    }
    .main-chat-list .media-contact-name span:first-child {
        font-weight: 600;
        color: #f1f1f1 !important;
        font-size: 15px;
    }
    .main-chat-list .time {
        font-size: 12px;
        color: #aaa !important;
    }
    .main-chat-list .media:hover, .main-chat-list .media.selected {
        background-color: rgba(207, 162, 103, 0.1);
        border-color: rgba(207, 162, 103, 0.3);
    }
    .main-chat-list .media-contact-name {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 5px;
    }
    .main-chat-list .media-contact-name span:first-child {
        font-weight: 600;
        color: #f1f1f1;
        font-size: 15px;
    }
    .main-chat-list .time {
        font-size: 11px;
        color: #888;
    }
    .main-chat-list .media-body {
        margin-left: 15px;
    }
    .main-img-user {
        position: relative;
        width: 45px;
        height: 45px;
    }
    .main-img-user img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
        border: 2px solid #2b2e38;
    }
    .main-img-user .dot-label {
        position: absolute;
        bottom: 2px;
        right: 2px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        border: 2px solid #2b2e38;
    }
    .bg-success { background-color: #28a745 !important; }
    .bg-danger { background-color: #dc3545 !important; }

    /* Chat Right Pane */
    .main-content-body-chat {
        display: flex;
        flex-direction: column;
        background-color: #1e2027;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    .main-chat-header {
        padding: 20px;
        border-bottom: 1px solid rgba(255,255,255,0.05);
        background-color: #23262f;
        align-items: center;
    }
    .main-chat-header .main-img-user { margin-right: 15px; }
    .main-chat-msg-name h6 { margin-bottom: 2px; font-size: 16px; font-weight: 600; color: #fff; }
    .main-chat-msg-name small { color: #888; font-size: 12px; }

    /* Chat Body */
    .main-chat-body {
        padding: 20px;
        flex: 1;
        background-color: #181a20;
    }
    .content-inner {
        max-height: 550px;
        overflow-y: auto;
        padding-right: 10px;
    }
    /* Scrollbar styling */
    .content-inner::-webkit-scrollbar { width: 6px; }
    .content-inner::-webkit-scrollbar-track { background: transparent; }
    .content-inner::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }
    
    .media.chat-left, .media.chat-right {
        margin-bottom: 20px;
        display: flex;
        align-items: flex-end;
    }
    .media.chat-right {
        flex-direction: row-reverse;
    }
    .media.chat-left .main-msg-wrapper {
        background-color: #2b2e38;
        color: #e4e4e4;
        padding: 12px 18px;
        border-radius: 18px 18px 18px 4px;
        margin-left: 10px;
        font-size: 14px;
        max-width: 75%;
        word-break: break-word;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .media.chat-right .main-msg-wrapper {
        background-color: #CFA267;
        color: #fff;
        padding: 12px 18px;
        border-radius: 18px 18px 4px 18px;
        margin-right: 10px;
        font-size: 14px;
        max-width: 75%;
        word-break: break-word;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .chat-timestamp {
        font-size: 11px;
        color: #777;
        margin-top: 5px;
        display: block;
    }
    .media.chat-right .chat-timestamp { text-align: right; margin-right: 15px; }
    .media.chat-left .chat-timestamp { margin-left: 15px; }

    /* Chat Footer / Input */
    .main-chat-footer {
        padding: 15px 20px;
        background-color: #23262f;
        border-top: 1px solid rgba(255,255,255,0.05);
        display: flex;
        align-items: center;
        gap: 15px;
    }
    .main-chat-footer .form-control {
        background-color: #181a20;
        border: 1px solid rgba(255,255,255,0.1);
        color: #fff;
        border-radius: 25px;
        padding: 12px 20px;
        flex: 1;
    }
    .main-chat-footer .form-control:focus {
        border-color: #CFA267;
        box-shadow: 0 0 0 0.2rem rgba(207, 162, 103, 0.25);
    }
    .btn-icon.btn-primary {
        background-color: #CFA267;
        border-color: #CFA267;
        width: 45px;
        height: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: all 0.3s ease;
    }
    .btn-icon.btn-primary:hover {
        background-color: #b58d58;
        transform: translateY(-2px);
    }
    .file-attach-btn {
        cursor: pointer;
        color: #888;
        font-size: 20px;
        transition: color 0.3s;
        margin-bottom: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: rgba(255,255,255,0.05);
    }
    .file-attach-btn:hover {
        color: #CFA267;
        background: rgba(207, 162, 103, 0.1);
    }
</style>
@endpush
@section('content')
<!--app-content open-->
<div class="app-content main-content mt-0">
    <div class="side-app">

        <!-- CONTAINER -->
        <div class="main-container container-fluid">


            <!-- PAGE-HEADER -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">{{ $crud ? ucwords(str_replace('_', ' ', $crud)) : 'N/A' }}</h1>
                </div>
                <div class="ms-auto pageheader-btn">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url("admin/dashboard") }}"><i class="fe fe-home me-2 fs-14"></i>Home</a></li>
                        <li class="breadcrumb-item"><a href="javascript:void(0);">Apps</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Chat</li>
                    </ol>
                </div>
            </div>
            <!-- PAGE-HEADER END -->

            <!-- Row -->
            <div class="row row-deck">
                <div class="col-sm-12 col-md-4">
                    <div class="card  overflow-scroll">
                        <div class="main-content-app pt-0">
                            <div class="main-content-left main-content-left-chat">

                                <!-- main-chat-header -->
                                <div class="card-body overflow-scroll border-bottom">
                                    <div class="input-group mb-2">
                                        <form action="" method="get">
                                            <div class="input-group">
                                                <input name="keyword" type="text" id="keyword" class="form-control" placeholder="Search ...">
                                                <button type="button" class="btn btn-primary text-white" onclick="userSearch();">Search</button>
                                                <button type="button" class="btn btn-secondary text-white" onclick="userList();">Refresh</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <!-- main-chat-list -->
                                <div class="tab-content main-chat-list flex-2">
                                    <div class="tab-pane active" id="ChatList">
                                        <div class="main-chat-list tab-pane" id="userList"></div>
                                        <!-- main-chat-list -->
                                    </div>
                                </div>
                                <!-- main-chat-list -->
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 col-md-8">
                    <div class="card">
                        <div class="main-content-app pt-0" style="position: relative; min-height: 550px;">
                            <!-- Placeholder when no chat is selected -->
                            <div class="d-flex justify-content-center align-items-center w-100" id="ChatPlaceholder" style="position: absolute; top: 0; left: 0; bottom: 0; right: 0; flex-direction: column; background: #181a20; border-radius: 12px; z-index: 5;">
                                <i class="fa-regular fa-comments" style="font-size: 80px; color: rgba(255,255,255,0.1); margin-bottom: 20px;"></i>
                                <h4 style="color: rgba(255,255,255,0.4);">Select a user to start chatting</h4>
                            </div>

                            <div class="main-content-body main-content-body-chat h-100 d-none" id="ChatBox" style="position: relative; z-index: 10;">
                                <div class="main-chat-header pt-3 d-block d-sm-flex">
                                    <div class="main-img-user online" id="ReceiverImage"></div>
                                    <div class="main-chat-msg-name mt-2">
                                        <p class="mb-0" id="ReceiverName" onclick="userChat($('#ReceiverId').val());" style="cursor: pointer;">User</p>
                                        <small class="me-3" id="ReceiverRoll">Roll</small>
                                    </div>
                                </div>
                                <!-- main-chat-header -->
                                <div class="main-chat-body flex-2" id="ChatBody">
                                    <div class="content-inner" id="ChatContent" style="max-height: 500px; overflow-y: auto;"></div>
                                </div>
                                <div class="main-chat-footer">
                                    <label for="File" id="FileLabel" class="file-attach-btn" title="Attach Image"><i class="bi bi-image"></i></label>
                                    <input type="file" id="File" hidden accept=".jpg,.jpeg,.png,.gif">
                                    <input class="form-control" placeholder="Type your message here..." type="text" id="Text">
                                    <input type="text" hidden id="ReceiverId" />
                                    <input type="text" hidden id="RoomId" />
                                    <button type="button" class="btn-icon btn-primary" onclick="sendMessage($('#ReceiverId').val())"><i class="bi bi-send"></i></button>
                                    <button type="button" class="btn-icon btn-primary" onclick="formClear()"><i class="bi bi-arrow-clockwise"></i></button>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
<!-- CONTAINER CLOSED -->
@endsection

@push('scripts')
<!-- Internal Chat js-->
<!-- <script src="{{ asset('backend/js/chat.js') }}"></script> -->
<script src="https://cdn.jsdelivr.net/npm/dayjs/dayjs.min.js"></script>
<script>
    function userList() {
        NProgress.start();
        $.ajax({
            url: `{{ route('admin.chat.list') }}`,
            type: "GET",
            success: function(response) {
                NProgress.done();
                $('#userList').empty();
                $.each(response.data.users, function(index, value) {
                    let senderAvatar = value.avatar ? `{{ asset('${value.avatar}') }}` : "{{ asset('default/profile.jpg') }}";
                    $('#userList').append(`
                        <a class="media new" href="javascript:void(0)" onclick="userChat(${value.id})">
                            <div class="main-img-user online">
                                <img alt="avatar" src="${senderAvatar}">
                                ${value.is_online ? '<span class="dot-label bg-success"></span>' : '<span class="dot-label bg-danger"></span>'}
                            </div>
                            <div class="media-body">
                                <div class="media-contact-name">
                                    <span>${value.name}</span>
                                    <span class="time">${value.last_chat?.humanize_date ?? ''}</span>
                                </div>
                                <span class="time" style="display:block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 180px;">${value.last_chat?.short_text ?? 'No messages yet...'}</span>
                            </div>
                        </a>
                    `);
                });
            },
            error: function(xhr, status, error) {
                console.log('Error sending message:', error);
            }
        });
    }

    userList();

    function userSearch() {
        NProgress.start();
        $('#userList').empty();
        let keyword = $('#keyword').val();
        $.ajax({
            url: `{{ route('admin.chat.search') }}?keyword=${keyword}`,
            type: "GET",
            success: function(response) {
                NProgress.done();
                $.each(response.data.users, function(index, value) {
                    let senderAvatar = value.avatar ? `{{ asset('${value.avatar}') }}` : "{{ asset('default/profile.jpg') }}";
                    $('#userList').append(`
                        <a class="media new" href="javascript:void(0)" onClick="userChat(${value.id})">
                            <div class="main-img-user online">
                                <img alt="avatar" src="${senderAvatar}">
                            </div>
                            <div class="media-body">
                                <div class="media-contact-name">
                                    <span>${value.name}</span>
                                </div>
                                <span class="time" style="display:block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 180px;">${value.email}</span>
                            </div>
                        </a>
                    `);
                });
            },
            error: function(xhr, status, error) {
                console.log('Error sending message:', error);
            }
        });
    }

    function userChat(receiver_id) {
        NProgress.start();
        $.ajax({
            url: `{{ route('admin.chat.conversation', ':id') }}`.replace(':id', receiver_id),
            type: "GET",
            success: function(response) {
                NProgress.done();
                $('#ChatContent').empty();
                $('#ReceiverId').val(receiver_id);
                $('#ReceiverName').text(response.data.receiver.name);
                $('#ReceiverRoll').text(response.data.receiver.role);
                $('#RoomId').val(response.data.room.id);
                window.sessionStorage.setItem('room_id', response.data.room.id);
                $('#ChatPlaceholder').addClass('d-none');
                $('#ChatBox').removeClass('d-none');

                if ($('#selectUser' + receiver_id).length) {
                    $('.selected').not('#selectUser' + receiver_id).removeClass('selected');
                    $('#selectUser' + receiver_id).addClass('selected');
                }

                let receiverAvatar = response.data.receiver.avatar ? `{{ asset('${response.data.receiver.avatar}') }}` : "{{ asset('default/profile.jpg') }}";
                let senderAvatar = response.data.sender.avatar ? `{{ asset('${response.data.sender.avatar}') }}` : "{{ asset('default/profile.jpg') }}";

                $('#ReceiverImage').html(`<img alt="avatar" src="${receiverAvatar}">`);

                let senderClass = 'media flex-row-reverse chat-right';
                let receiverClass = 'media chat-left';

                let chatData = response.data.chat.data ? response.data.chat.data : response.data.chat;
                chatData.reverse().forEach(chat => {
                    let chatClass = chat.sender_id == `{{auth('web')->user()->id}}` ? senderClass : receiverClass;
                    let avatar = chat.sender_id == `{{auth('web')->user()->id}}` ? senderAvatar : receiverAvatar;
                    $('#ChatContent').append(`
                    <div class="${chatClass}">
                        <div class="main-img-user online"><img alt="avatar" src="${avatar}"></div>
                        <div class="media-body">
                            ${chat.text ? `<div class="main-msg-wrapper">${chat.text}</div>` : ''}
                            ${chat.file ? `<div class="main-msg-wrapper"><a href="${chat.file}" target="_blank"><img src="${chat.file}" style="max-width: 200px; border-radius: 8px;"></a></div>` : ''}
                            <span class="chat-timestamp">${chat.humanize_date || ''}</span>
                        </div>
                    </div>
                `);
                });

                $('#ChatContent').scrollTop($('#ChatContent')[0].scrollHeight);
            },
            error: function(xhr, status, error) {
                console.error('Error sending message:', error);
            }
        });
    }

    $('#File').on('change', function() {
        let file = this.files[0];
        let reader = new FileReader();
        reader.onload = function(e) {
            $('#FileLabel').html(`<img src="${e.target.result}" style="width: 20px; height: 20px;"/>`);
        };
        reader.readAsDataURL(file);
    });

    function formClear() {
        NProgress.start();
        $('#FileLabel').html(`<i class="bi bi-image"></i>`);
        $('#File').val('');
        $('#Text').val('');
        NProgress.done();
        toastr.success('Form Clear');
    }

    function sendMessage(receiver_id) {
        NProgress.start();
        let text = $('#Text').val() || null;
        let file = $('#File')[0].files[0] || null;
        if (text !== null || file !== null) {
            let formData = new FormData();
            if (text !== null) {
                formData.append('text', text);
            }
            if (file !== null) {
                formData.append('file', file);
            }

            $.ajax({
                url: `{{ route('admin.chat.send', ':id') }}`.replace(':id', receiver_id),
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    NProgress.done();
                    $('#Text').val('');
                    $('#File').val('');
                    $('#FileLabel').html(`<i class="bi bi-image"></i>`);
                    userChat(receiver_id);
                    userList();
                },
                error: function(xhr, status, error) {
                    console.log('Error sending message:', error);
                }
            });
        }
    }

    setInterval(() => {
        userList();
    }, 300000);

    /* document.addEventListener('DOMContentLoaded', function() {
        const roomId = window.sessionStorage.getItem('room_id');
        Echo.private(`chat-room.${roomId}`).listen('MessageSendEvent', function(e) {
            userChat(document.getElementById('ReceiverId').value);
            userList();
        });
    }); */

    var user_id = `{{ auth('web')->check() ? auth('web')->user()->id : null }}`;

    if (user_id) {
        document.addEventListener('DOMContentLoaded', function() {
            Echo.private(`chat-receiver.${user_id}`).listen('MessageSendEvent', function(e) {
                toastr.success(e.data.text ?? "File Sent");
                let receiver_id = document.getElementById('ReceiverId').value;
                if (receiver_id) {
                    userChat(receiver_id);
                }
                userList();
            });
        });
    }
</script>
@endpush