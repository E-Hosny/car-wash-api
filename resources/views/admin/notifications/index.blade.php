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
</script>
@endsection
