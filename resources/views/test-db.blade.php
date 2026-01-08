<!DOCTYPE html>
<html>
<head><title>Database Test</title></head>
<body>
    <h1>Database Test</h1>
    
    <h2>Clients ({{ $clients->count() }})</h2>
    <ul>
        @foreach($clients as $client)
            <li>{{ $client->email }} ({{ $client->websites_count }} websites)</li>
        @endforeach
    </ul>
    
    <h2>Sample Websites ({{ $websites->count() }})</h2>
    <ul>
        @foreach($websites as $website)
            <li>
                {{ $website->url }} 
                - Status: <span style="color: {{ $website->status === 'up' ? 'green' : ($website->status === 'down' ? 'red' : 'gray') }}">
                    {{ $website->status }}
                </span>
                - Last checked: {{ $website->last_checked_at ? $website->last_checked_at->diffForHumans() : 'Never' }}
            </li>
        @endforeach
    </ul>
</body>
</html>