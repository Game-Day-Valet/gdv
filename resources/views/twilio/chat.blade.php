@extends('layouts.vertical', ['title' => 'Twilio Chat'])

@section('css')
<style>
.chat-container{display:flex;gap:16px}
.chat-list{width:320px;max-width:32%;border-right:1px solid #eef2f7;height:70vh;overflow:auto}
.chat-item{padding:10px 12px;border-bottom:1px solid #f3f4f6;cursor:pointer}
.chat-item.active{background:#f8fafc}
.chat-window{flex:1;display:flex;flex-direction:column;height:70vh}
.messages{flex:1;overflow:auto;padding:12px;background:#f8fafc;position:relative}
.bubble{max-width:70%;padding:10px 12px;border-radius:14px;margin-bottom:10px;display:inline-block;position:relative}
.bubble.me{background:#4f46e5;color:#fff;border-bottom-right-radius:4px;margin-left:auto}
.bubble.them{background:#ffffff;border:1px solid #e5e7eb;border-bottom-left-radius:4px}
.bubble .ts{display:none;position:absolute;right:0;margin-bottom:6px;margin-top:6px;font-size:12px;color:#111827;white-space:nowrap;background:#ffffff;border:1px solid #e5e7eb;border-radius:6px;padding:6px 8px;box-shadow:0 4px 12px rgba(0,0,0,0.08);z-index:10000}
.bubble .ts.top{bottom:100%}
.bubble .ts.bottom{top:100%}
.bubble .ts.top:after{content:"";position:absolute;top:100%;right:10px;border-width:6px;border-style:solid;border-color:#ffffff transparent transparent transparent;filter:drop-shadow(0 -1px 0 #e5e7eb)}
.bubble .ts.bottom:after{content:"";position:absolute;bottom:100%;right:10px;border-width:6px;border-style:solid;border-color:transparent transparent #ffffff transparent;filter:drop-shadow(0 1px 0 #e5e7eb)}
.bubble.them .ts{left:0;right:auto}
.bubble.them .ts.top:after{left:10px;right:auto}
.bubble.them .ts.bottom:after{left:10px;right:auto}
.bubble:hover .ts{display:block}
.bubble .body{white-space:pre-wrap;word-wrap:break-word}
.composer{display:flex;gap:8px;padding:10px;border-top:1px solid #e5e7eb;background:#fff}
</style>
@endsection

@section('content')
<div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
    <div class="flex-grow-1">
        <h4 class="fs-18 fw-semibold m-0">Twilio Chat</h4>
    </div>
    <div class="text-end">
        <ol class="breadcrumb m-0 py-0">
            <li class="breadcrumb-item"><a href="javascript:void(0)">Messaging</a></li>
            <li class="breadcrumb-item active">Twilio Chat</li>
        </ol>
    </div>
    </div>

@if($error)
    <div class="alert alert-danger">{{ $error }}</div>
@endif

<div class="card">
    <div class="card-body chat-container">
        <div class="chat-list" id="chatList">
            @foreach($conversations as $c)
                <div class="chat-item {{ $selectedPhone === $c['phone'] ? 'active' : '' }}" data-phone="{{ $c['phone'] }}">
                    <div class="fw-semibold">{{ $c['label'] }}</div>
                    <div class="text-muted" style="font-size:12px">{{ $c['last_body'] }}</div>
                    <div class="text-muted" style="font-size:11px">{{ $c['last_time'] }}</div>
                </div>
            @endforeach
        </div>
        <div class="chat-window">
            <div class="messages" id="messages"></div>
            <div class="composer">
                <input type="text" id="composeInput" class="form-control" placeholder="Type a message...">
                <button id="sendBtn" class="btn btn-primary">Send</button>
            </div>
        </div>
    </div>
</div>

<script>
const messagesEl = document.getElementById('messages');
const chatList = document.getElementById('chatList');
const inputEl = document.getElementById('composeInput');
const sendBtn = document.getElementById('sendBtn');
let currentPhone = '{{ $selectedPhone }}' || (chatList.querySelector('.chat-item')?.getAttribute('data-phone')||'');

function renderMessages(list){
  messagesEl.innerHTML='';
  list.forEach(m=>{
    const div=document.createElement('div');
    div.className='bubble '+(m.mine?'me':'them');
    const body=document.createElement('div');
    body.className='body';
    body.textContent=m.body;
    const ts=document.createElement('div');
    ts.className='ts bottom';
    ts.textContent = new Date(m.time?.replace(' ','T')+'Z').toLocaleString();
    div.appendChild(body);
    div.appendChild(ts);
    const wrap=document.createElement('div');
    wrap.appendChild(div);
    messagesEl.appendChild(wrap);
  });
  messagesEl.scrollTop=messagesEl.scrollHeight;
}

async function loadMessages(){
  if(!currentPhone) return;
  try{
    const res = await fetch(`{{ url('/admin/twilio/chat/messages') }}?phone=${encodeURIComponent(currentPhone)}`);
    const data = await res.json();
    if(data.success){ renderMessages(data.messages); }
  }catch(e){ /* ignore */ }
}

async function sendMessage(){
  const body = inputEl.value.trim();
  if(!body || !currentPhone) return;
  sendBtn.disabled=true;
  try{
    const res = await fetch(`{{ url('/admin/twilio/chat/send') }}`,{
      method:'POST',
      headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
      body: JSON.stringify({ phone: currentPhone, body })
    });
    const data = await res.json();
    if(data.success){ inputEl.value=''; loadMessages(); }
  }catch(e){ /* ignore */ }
  finally{ sendBtn.disabled=false; }
}

chatList.addEventListener('click',(e)=>{
  const item=e.target.closest('.chat-item');
  if(!item) return;
  chatList.querySelectorAll('.chat-item').forEach(el=>el.classList.remove('active'));
  item.classList.add('active');
  currentPhone=item.getAttribute('data-phone');
  loadMessages();
});

sendBtn.addEventListener('click', sendMessage);
inputEl.addEventListener('keydown', e=>{ if(e.key==='Enter'){ sendMessage(); }});

// initial load and polling
loadMessages();
setInterval(loadMessages, 5000);

// Smart tooltip placement: flip to top only if enough space above
messagesEl.addEventListener('mouseenter', function(e){
  const bubble = e.target.closest('.bubble');
  if(!bubble) return;
  const tip = bubble.querySelector('.ts');
  if(!tip) return;
  // temporarily show to measure
  tip.style.display='block';
  const msgRect = messagesEl.getBoundingClientRect();
  const bubRect = bubble.getBoundingClientRect();
  const tipRect = tip.getBoundingClientRect();
  const spaceAbove = bubRect.top - msgRect.top;
  if (spaceAbove > (tipRect.height + 16)) {
    tip.classList.remove('bottom');
    tip.classList.add('top');
  } else {
    tip.classList.remove('top');
    tip.classList.add('bottom');
  }
  tip.style.display='';
}, true);
</script>
@endsection


