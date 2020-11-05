<h1>Your Wallets</h1>

@forelse ($wallets as $wallet)
    <li>{{ $wallet->name }} ({{ $wallet->display_balance }})</li>
@empty
    <p> 'No wallets yet' </p>
@endforelse
