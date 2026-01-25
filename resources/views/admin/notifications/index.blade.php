@extends('admin.layout')

@section('content')
<div class="container mt-4">
    <h2 class="text-center mb-4">الإشعارات / Notifications</h2>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- OneSignal Test Section -->
    <div class="row">
        <div class="col-12">
            <div class="card border-secondary shadow-sm">
                <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-bell"></i> OneSignal Push Notification Test
                    </h5>
                    <a href="{{ route('admin.onesignal.get.players') }}" class="btn btn-light btn-sm">
                        <i class="bi bi-arrow-clockwise"></i> Refresh Players
                    </a>
                </div>
                <div class="card-body">
                    <!-- Android Heads-up Notification Note -->
                    <div class="alert alert-info mb-3">
                        <i class="bi bi-info-circle"></i> 
                        <strong>Android Heads-up Notifications:</strong><br>
                        1. Create Android Category in OneSignal Dashboard: Settings > Push & In-App > Android Notification Channels<br>
                        2. Set <strong>Importance: Urgent</strong> and <strong>Sound: Default</strong> in the category<br>
                        3. Copy the Channel ID and add it to <code>ONESIGNAL_ANDROID_CHANNEL_ID</code> in .env<br>
                        4. <strong>Important:</strong> After creating/updating channel, uninstall/reinstall or clear app data to test new settings.
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <h6 class="mb-3">Send to All Users</h6>
                            <form id="onesignalTestForm" action="{{ route('admin.onesignal.test.send') }}" method="POST">
                                @csrf
                                <button type="submit" id="onesignalTestBtn" class="btn btn-primary btn-lg w-100">
                                    <i class="bi bi-send"></i> Send Push Test to All
                                </button>
                            </form>
                        </div>
                        <div class="col-md-4">
                            <h6 class="mb-3">Send to User (by User ID)</h6>
                            <form id="onesignalUserForm" action="{{ route('admin.onesignal.send.to.user') }}" method="POST">
                                @csrf
                                <div class="mb-2">
                                    <label for="user_id" class="form-label">Select User:</label>
                                    <select class="form-select" id="user_id" name="user_id" required>
                                        <option value="">-- Select User --</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}">
                                                {{ $user->name }} ({{ $user->email }}) - {{ $user->role }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <label for="user_title" class="form-label">Title (optional):</label>
                                    <input type="text" class="form-control" id="user_title" name="title" placeholder="Test Notification">
                                </div>
                                <div class="mb-2">
                                    <label for="user_message" class="form-label">Message (optional):</label>
                                    <input type="text" class="form-control" id="user_message" name="message" placeholder="Hello from Laravel ✅">
                                </div>
                                <button type="submit" id="onesignalUserBtn" class="btn btn-success w-100">
                                    <i class="bi bi-send"></i> Send to User
                                </button>
                            </form>
                        </div>
                        <div class="col-md-4">
                            <h6 class="mb-3">Send to Specific Player</h6>
                            <form id="onesignalPlayerForm" action="{{ route('admin.onesignal.send.to.player') }}" method="POST">
                                @csrf
                                <div class="mb-2">
                                    <label for="player_id" class="form-label">Player ID:</label>
                                    <input type="text" class="form-control" id="player_id" name="player_id" placeholder="Enter Player ID" required>
                                </div>
                                <div class="mb-2">
                                    <label for="player_title" class="form-label">Title (optional):</label>
                                    <input type="text" class="form-control" id="player_title" name="title" placeholder="Test Notification">
                                </div>
                                <div class="mb-2">
                                    <label for="player_message" class="form-label">Message (optional):</label>
                                    <input type="text" class="form-control" id="player_message" name="message" placeholder="Hello from Laravel ✅">
                                </div>
                                <button type="submit" id="onesignalPlayerBtn" class="btn btn-warning w-100">
                                    <i class="bi bi-send"></i> Send to Player
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Subscribed Players List -->
                    @if(!empty($onesignal_players))
                        <div class="mt-4">
                            <h6 class="mb-3">Subscribed Players ({{ count($onesignal_players) }})</h6>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Player ID</th>
                                            <th>User ID (External ID)</th>
                                            <th>Device Type</th>
                                            <th>Last Active</th>
                                            <th>Subscribed</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($onesignal_players as $player)
                                            @php
                                                $externalId = $player['external_user_id'] ?? $player['external_id'] ?? null;
                                                $user = null;
                                                if ($externalId) {
                                                    $user = \App\Models\User::find($externalId);
                                                }
                                            @endphp
                                            <tr>
                                                <td>
                                                    <code style="font-size: 0.85rem;">{{ $player['id'] ?? 'N/A' }}</code>
                                                    <button class="btn btn-sm btn-outline-secondary ms-2" onclick="copyToClipboard('{{ $player['id'] ?? '' }}')" title="Copy Player ID">
                                                        <i class="bi bi-clipboard"></i>
                                                    </button>
                                                </td>
                                                <td>
                                                    @if($externalId)
                                                        <code style="font-size: 0.85rem;">{{ $externalId }}</code>
                                                        @if($user)
                                                            <br><small class="text-muted">{{ $user->name }} ({{ $user->email }})</small>
                                                        @endif
                                                        <button class="btn btn-sm btn-outline-secondary ms-2" onclick="copyToClipboard('{{ $externalId }}')" title="Copy User ID">
                                                            <i class="bi bi-clipboard"></i>
                                                        </button>
                                                    @else
                                                        <span class="text-muted">Not linked</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if(isset($player['device_type']))
                                                        @if($player['device_type'] == 0)
                                                            <span class="badge bg-info">iOS</span>
                                                        @elseif($player['device_type'] == 1)
                                                            <span class="badge bg-success">Android</span>
                                                        @elseif($player['device_type'] == 2)
                                                            <span class="badge bg-primary">Web</span>
                                                        @else
                                                            <span class="badge bg-secondary">{{ $player['device_type'] }}</span>
                                                        @endif
                                                    @else
                                                        <span class="text-muted">N/A</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if(isset($player['last_active']))
                                                        {{ date('Y-m-d H:i', $player['last_active']) }}
                                                    @else
                                                        <span class="text-muted">N/A</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if(isset($player['invalid_identifier']) && $player['invalid_identifier'])
                                                        <span class="badge bg-danger">Invalid</span>
                                                    @elseif(isset($player['subscribable']) && !$player['subscribable'])
                                                        <span class="badge bg-warning">Not Subscribable</span>
                                                    @else
                                                        <span class="badge bg-success">Subscribed</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($externalId)
                                                        <button class="btn btn-sm btn-success mb-1" onclick="sendToUserFromTable('{{ $externalId }}')" title="Send using User ID">
                                                            <i class="bi bi-person-check"></i> Send (User)
                                                        </button>
                                                        <br>
                                                    @endif
                                                    <button class="btn btn-sm btn-primary" onclick="sendToPlayer('{{ $player['id'] ?? '' }}')" title="Send using Player ID">
                                                        <i class="bi bi-send"></i> Send (Player)
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @else
                        <div class="mt-4">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> No players loaded. Click "Refresh Players" to fetch subscribed users from OneSignal.
                            </div>
                        </div>
                    @endif

                    @if(session('onesignal_last_response'))
                        <div class="mt-4">
                            <h6 class="mb-2">Last Response (Debug):</h6>
                            <div class="card bg-light">
                                <div class="card-body">
                                    <pre id="onesignalResponse" class="mb-0" style="max-height: 400px; overflow-y: auto; white-space: pre-wrap; word-wrap: break-word;">{{ json_encode(session('onesignal_last_response'), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Order Payment Notification Settings Section -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-primary shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-gear"></i> Order Payment Notification Settings
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-3">
                        <i class="bi bi-info-circle"></i> 
                        <strong>About:</strong> Configure the push notification that will be automatically sent to customers when they complete an order payment. You can use placeholders to personalize the message.
                    </div>

                    <form id="orderPaymentSettingsForm" action="{{ route('admin.notifications.order-payment-settings') }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="order_payment_title" class="form-label">
                                <strong>Notification Title</strong>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="order_payment_title" 
                                   name="title" 
                                   value="{{ old('title', $order_payment_title ?? 'تم إتمام الطلب بنجاح') }}" 
                                   required
                                   maxlength="255">
                            <small class="form-text text-muted">The title of the push notification</small>
                        </div>

                        <div class="mb-3">
                            <label for="order_payment_message" class="form-label">
                                <strong>Notification Message</strong>
                            </label>
                            <textarea class="form-control" 
                                      id="order_payment_message" 
                                      name="message" 
                                      rows="3" 
                                      required
                                      maxlength="500">{{ old('message', $order_payment_message ?? 'تم إتمام طلبك رقم {order_id} بنجاح. المبلغ: {total}') }}</textarea>
                            <small class="form-text text-muted">The message body of the push notification</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><strong>Available Placeholders:</strong></label>
                            <div class="card bg-light">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <code>{order_id}</code> - Order number
                                        </div>
                                        <div class="col-md-4">
                                            <code>{total}</code> - Order total amount
                                        </div>
                                        <div class="col-md-4">
                                            <code>{customer_name}</code> - Customer name
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><strong>Preview:</strong></label>
                            <div class="card bg-light">
                                <div class="card-body">
                                    <div class="mb-2">
                                        <strong>Title:</strong> <span id="preview_title">-</span>
                                    </div>
                                    <div>
                                        <strong>Message:</strong> <span id="preview_message">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" id="saveSettingsBtn" class="btn btn-primary">
                            <i class="bi bi-save"></i> Save Settings
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Completion Rating Notification Settings Section -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-warning shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-star"></i> Order Completion Rating Notification Settings
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-3">
                        <i class="bi bi-info-circle"></i> 
                        <strong>About:</strong> Configure the push notification that will be automatically sent to customers when their order status is changed to "completed" by the provider. This notification includes a deep link to the app rating screen. You can use placeholders to personalize the message.
                    </div>

                    <form id="orderCompletionRatingSettingsForm" action="{{ route('admin.notifications.order-completion-rating-settings') }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="order_completion_rating_title" class="form-label">
                                <strong>Notification Title</strong>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="order_completion_rating_title" 
                                   name="title" 
                                   value="{{ old('title', $order_completion_rating_title ?? 'تم إكمال طلبك') }}" 
                                   required
                                   maxlength="255">
                            <small class="form-text text-muted">The title of the push notification</small>
                        </div>

                        <div class="mb-3">
                            <label for="order_completion_rating_message" class="form-label">
                                <strong>Notification Message</strong>
                            </label>
                            <textarea class="form-control" 
                                      id="order_completion_rating_message" 
                                      name="message" 
                                      rows="3" 
                                      required
                                      maxlength="500">{{ old('message', $order_completion_rating_message ?? 'تم إكمال طلبك رقم {order_id}. شاركنا رأيك وقيم تجربتك!') }}</textarea>
                            <small class="form-text text-muted">The message body of the push notification</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><strong>Available Placeholders:</strong></label>
                            <div class="card bg-light">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <code>{order_id}</code> - Order number
                                        </div>
                                        <div class="col-md-6">
                                            <code>{customer_name}</code> - Customer name
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><strong>Preview:</strong></label>
                            <div class="card bg-light">
                                <div class="card-body">
                                    <div class="mb-2">
                                        <strong>Title:</strong> <span id="preview_rating_title">-</span>
                                    </div>
                                    <div>
                                        <strong>Message:</strong> <span id="preview_rating_message">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" id="saveRatingSettingsBtn" class="btn btn-warning">
                            <i class="bi bi-save"></i> Save Settings
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('onesignalTestForm');
    const btn = document.getElementById('onesignalTestBtn');
    
    if (form && btn) {
        form.addEventListener('submit', function(e) {
            // Disable button and show loading state
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Sending...';
        });
    }

    const playerForm = document.getElementById('onesignalPlayerForm');
    const playerBtn = document.getElementById('onesignalPlayerBtn');
    
    if (playerForm && playerBtn) {
        playerForm.addEventListener('submit', function(e) {
            playerBtn.disabled = true;
            playerBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Sending...';
        });
    }

    const userForm = document.getElementById('onesignalUserForm');
    const userBtn = document.getElementById('onesignalUserBtn');
    
    if (userForm && userBtn) {
        userForm.addEventListener('submit', function(e) {
            userBtn.disabled = true;
            userBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Sending...';
        });
    }
});

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('Player ID copied to clipboard!');
    }, function(err) {
        console.error('Failed to copy: ', err);
    });
}

function sendToPlayer(playerId) {
    if (!playerId) {
        alert('Player ID is missing!');
        return;
    }
    
    document.getElementById('player_id').value = playerId;
    document.getElementById('player_title').value = 'Test Notification';
    document.getElementById('player_message').value = 'Hello from Laravel ✅';
    
    // Scroll to form
    document.getElementById('player_id').scrollIntoView({ behavior: 'smooth', block: 'center' });
    document.getElementById('player_id').focus();
}

function sendToUserFromTable(userId) {
    if (!userId) {
        alert('User ID is missing!');
        return;
    }
    
    document.getElementById('user_id').value = userId;
    document.getElementById('user_title').value = 'Test Notification';
    document.getElementById('user_message').value = 'Hello from Laravel ✅';
    
    // Scroll to form
    document.getElementById('user_id').scrollIntoView({ behavior: 'smooth', block: 'center' });
    document.getElementById('user_id').focus();
}

// Order Payment Settings Preview
function updatePreview() {
    const title = document.getElementById('order_payment_title').value || '';
    const message = document.getElementById('order_payment_message').value || '';
    
    // Replace placeholders with example values
    const previewTitle = title
        .replace(/{order_id}/g, '123')
        .replace(/{total}/g, '150.00')
        .replace(/{customer_name}/g, 'أحمد محمد');
    
    const previewMessage = message
        .replace(/{order_id}/g, '123')
        .replace(/{total}/g, '150.00')
        .replace(/{customer_name}/g, 'أحمد محمد');
    
    document.getElementById('preview_title').textContent = previewTitle || '-';
    document.getElementById('preview_message').textContent = previewMessage || '-';
}

// Update preview on input change
document.addEventListener('DOMContentLoaded', function() {
    const titleInput = document.getElementById('order_payment_title');
    const messageInput = document.getElementById('order_payment_message');
    
    if (titleInput) {
        titleInput.addEventListener('input', updatePreview);
        titleInput.addEventListener('keyup', updatePreview);
    }
    
    if (messageInput) {
        messageInput.addEventListener('input', updatePreview);
        messageInput.addEventListener('keyup', updatePreview);
    }
    
    // Initial preview
    updatePreview();

    // Settings form submit handler
    const settingsForm = document.getElementById('orderPaymentSettingsForm');
    const saveBtn = document.getElementById('saveSettingsBtn');
    
    if (settingsForm && saveBtn) {
        settingsForm.addEventListener('submit', function(e) {
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...';
        });
    }

    // Order Completion Rating Settings Preview
    function updateRatingPreview() {
        const title = document.getElementById('order_completion_rating_title').value || '';
        const message = document.getElementById('order_completion_rating_message').value || '';
        
        // Replace placeholders with example values
        const previewTitle = title
            .replace(/{order_id}/g, '123')
            .replace(/{customer_name}/g, 'أحمد محمد');
        
        const previewMessage = message
            .replace(/{order_id}/g, '123')
            .replace(/{customer_name}/g, 'أحمد محمد');
        
        document.getElementById('preview_rating_title').textContent = previewTitle || '-';
        document.getElementById('preview_rating_message').textContent = previewMessage || '-';
    }

    // Update rating preview on input change
    const ratingTitleInput = document.getElementById('order_completion_rating_title');
    const ratingMessageInput = document.getElementById('order_completion_rating_message');
    
    if (ratingTitleInput) {
        ratingTitleInput.addEventListener('input', updateRatingPreview);
        ratingTitleInput.addEventListener('keyup', updateRatingPreview);
    }
    
    if (ratingMessageInput) {
        ratingMessageInput.addEventListener('input', updateRatingPreview);
        ratingMessageInput.addEventListener('keyup', updateRatingPreview);
    }
    
    // Initial rating preview
    updateRatingPreview();

    // Rating settings form submit handler
    const ratingSettingsForm = document.getElementById('orderCompletionRatingSettingsForm');
    const saveRatingBtn = document.getElementById('saveRatingSettingsBtn');
    
    if (ratingSettingsForm && saveRatingBtn) {
        ratingSettingsForm.addEventListener('submit', function(e) {
            saveRatingBtn.disabled = true;
            saveRatingBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...';
        });
    }
});
</script>
@endsection
