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
.status-icon{font-size:12px;margin-top:4px;display:flex;gap:6px;align-items:center;justify-content:flex-end;color:#6b7280}
.status-icon.success{color:#16a34a}
.status-icon.error{color:#dc2626}
.status-icon.pending{color:#9ca3af}
.bubble .media{max-width:200px;margin:5px 0;border-radius:12px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.1)}
.bubble .media img{width:100%;height:auto;display:block;transition:transform 0.2s ease}
.bubble .media img:hover{transform:scale(1.02)}
.composer{display:flex;flex-direction:column;padding:16px;border-top:1px solid #e5e7eb;background:#fff;border-radius:0 0 8px 8px}
.media-preview{display:flex;gap:12px;margin-bottom:12px;flex-wrap:wrap;min-height:40px;align-items:center}
.media-preview-item{position:relative;width:80px;height:80px;border-radius:12px;overflow:hidden;border:2px solid #e5e7eb;box-shadow:0 2px 8px rgba(0,0,0,0.1);transition:all 0.2s ease}
.media-preview-item:hover{transform:translateY(-2px);box-shadow:0 4px 12px rgba(0,0,0,0.15)}
.media-preview-item img{width:100%;height:100%;object-fit:cover}
.media-preview-item .remove{position:absolute;top:-8px;right:-8px;background:#dc3545;color:white;border-radius:50%;width:24px;height:24px;display:flex;align-items:center;justify-content:center;font-size:14px;cursor:pointer;box-shadow:0 2px 4px rgba(0,0,0,0.2);transition:all 0.2s ease}
.media-preview-item .remove:hover{background:#c82333;transform:scale(1.1)}
.composer-input{display:flex;gap:12px;align-items:center}
.attach-btn{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);border:none;color:white;padding:12px 16px;border-radius:12px;cursor:pointer;transition:all 0.3s ease;box-shadow:0 2px 8px rgba(102,126,234,0.3)}
.attach-btn:hover{transform:translateY(-2px);box-shadow:0 4px 12px rgba(102,126,234,0.4)}
.attach-btn:active{transform:translateY(0)}
.send-btn{background:linear-gradient(135deg,#4f46e5 0%,#7c3aed 100%);border:none;color:white;padding:12px 24px;border-radius:12px;cursor:pointer;transition:all 0.3s ease;box-shadow:0 2px 8px rgba(79,70,229,0.3)}
.send-btn:hover{transform:translateY(-2px);box-shadow:0 4px 12px rgba(79,70,229,0.4)}
.send-btn:disabled{opacity:0.6;cursor:not-allowed;transform:none}
.message-input{flex:1;border:2px solid #e5e7eb;border-radius:12px;padding:12px 16px;font-size:14px;transition:all 0.2s ease;background:#fafafa}
.message-input:focus{outline:none;border-color:#4f46e5;background:#fff;box-shadow:0 0 0 3px rgba(79,70,229,0.1)}
.empty-state{text-align:center;padding:40px;color:#6b7280}
.empty-state i{font-size:48px;margin-bottom:16px;color:#d1d5db}
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
            <div class="messages" id="messages">
                <div class="empty-state" id="emptyState">
                    <i class="fas fa-comments"></i>
                    <div>Select a conversation to start chatting</div>
                </div>
            </div>
            <div class="composer">
                <div class="media-preview" id="mediaPreview"></div>
                <div class="composer-input">
                    <input type="file" id="imageInput" accept="image/*" style="display:none">
                    <button id="attachBtn" class="attach-btn" type="button" title="Attach Image">
                        <i class="fas fa-paperclip"></i>
                    </button>
                    <input type="text" id="composeInput" class="message-input" placeholder="Type a message...">
                    <button id="sendBtn" class="send-btn">
                        <i class="fas fa-paper-plane"></i> Send
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const messagesEl = document.getElementById('messages');
const chatList = document.getElementById('chatList');
const inputEl = document.getElementById('composeInput');
const sendBtn = document.getElementById('sendBtn');
const imageInput = document.getElementById('imageInput');
const attachBtn = document.getElementById('attachBtn');
const mediaPreview = document.getElementById('mediaPreview');
let currentPhone = '{{ $selectedPhone }}' || (chatList.querySelector('.chat-item')?.getAttribute('data-phone')||'');
let selectedImages = [];

function renderMessages(list){
  const emptyState = document.getElementById('emptyState');
  if(emptyState) emptyState.style.display = 'none';
  
  messagesEl.innerHTML='';
  if(list.length === 0) {
    const emptyDiv = document.createElement('div');
    emptyDiv.className = 'empty-state';
    emptyDiv.innerHTML = '<i class="fas fa-comments"></i><div>No messages yet. Start the conversation!</div>';
    messagesEl.appendChild(emptyDiv);
    return;
  }
  
  list.forEach(m=>{
    const div=document.createElement('div');
    div.className='bubble '+(m.mine?'me':'them');
    
    // Add media if exists
    if(m.media_urls && m.media_urls.length > 0){
      m.media_urls.forEach(url => {
        const mediaDiv = document.createElement('div');
        mediaDiv.className = 'media';
        const img = document.createElement('img');
        img.src = url;
        img.alt = 'Image';
        img.loading = 'lazy';
        mediaDiv.appendChild(img);
        div.appendChild(mediaDiv);
      });
    }
    
    // Add text body if exists
    if(m.body && m.body.trim()){
      const body=document.createElement('div');
      body.className='body';
      body.textContent=m.body;
      div.appendChild(body);
    }
    
    const ts=document.createElement('div');
    ts.className='ts bottom';
    ts.textContent = new Date(m.time?.replace(' ','T')+'Z').toLocaleString();
    div.appendChild(ts);

    // Delivery status icon (only for our outbound messages)
    if(m.mine){
      const statusWrap=document.createElement('div');
      statusWrap.className='status-icon ' + (m.status==='delivered' || m.status==='sent' ? 'success' : (m.status==='failed' || m.status==='undelivered' ? 'error' : 'pending'));
      statusWrap.title = 'Status: ' + (m.status || 'pending');
      const icon=document.createElement('i');
      if(m.status==='delivered' || m.status==='sent') icon.className='fas fa-check-circle';
      else if(m.status==='failed' || m.status==='undelivered') icon.className='fas fa-times-circle';
      else icon.className='fas fa-clock';
      statusWrap.appendChild(icon);
      div.appendChild(statusWrap);
    }
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
  if((!body && selectedImages.length === 0) || !currentPhone) return;
  sendBtn.disabled=true;
  try{
    const res = await fetch(`{{ url('/admin/twilio/chat/send') }}`,{
      method:'POST',
      headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
      body: JSON.stringify({ 
        phone: currentPhone, 
        body: body || null,
        media_urls: selectedImages.length > 0 ? selectedImages : null
      })
    });
    const data = await res.json();
    if(data.success){ 
      inputEl.value=''; 
      selectedImages = [];
      updateMediaPreview();
      loadMessages(); 
    }
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

// Image handling functions
async function uploadImage(file) {
  // Show loading state
  const loadingItem = document.createElement('div');
  loadingItem.className = 'media-preview-item';
  loadingItem.innerHTML = `
    <div style="display:flex;align-items:center;justify-content:center;width:100%;height:100%;background:#f3f4f6;color:#6b7280;">
      <i class="fas fa-spinner fa-spin"></i>
    </div>
  `;
  mediaPreview.appendChild(loadingItem);
  mediaPreview.style.display = 'flex';
  
  const formData = new FormData();
  formData.append('image', file);
  
  try {
    const res = await fetch(`{{ url('/admin/twilio/chat/upload') }}`, {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
      body: formData
    });
    const data = await res.json();
    if(data.success) {
      selectedImages.push(data.url);
      updateMediaPreview();
    } else {
      alert('Upload failed: ' + (data.message || 'Unknown error'));
      loadingItem.remove();
    }
  } catch(e) {
    console.error('Upload failed:', e);
    alert('Upload failed: ' + e.message);
    loadingItem.remove();
  }
}

function updateMediaPreview() {
  mediaPreview.innerHTML = '';
  if(selectedImages.length === 0) {
    mediaPreview.style.display = 'none';
    return;
  }
  
  mediaPreview.style.display = 'flex';
  selectedImages.forEach((url, index) => {
    const item = document.createElement('div');
    item.className = 'media-preview-item';
    item.innerHTML = `
      <img src="${url}" alt="Preview" loading="lazy">
      <div class="remove" onclick="removeImage(${index})" title="Remove image">
        <i class="fas fa-times"></i>
      </div>
    `;
    mediaPreview.appendChild(item);
  });
}

function removeImage(index) {
  selectedImages.splice(index, 1);
  updateMediaPreview();
}

// Event listeners
attachBtn.addEventListener('click', () => imageInput.click());
imageInput.addEventListener('change', (e) => {
  if(e.target.files.length > 0) {
    uploadImage(e.target.files[0]);
    e.target.value = ''; // Reset input
  }
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


