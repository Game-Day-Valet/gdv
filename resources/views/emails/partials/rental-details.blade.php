<div class="booking-details">
    <h3 class="section-title">Booking Details</h3>
    <div class="detail-row"><span class="detail-label">Tournament:</span><span class="detail-value">{{ $tournament->name ?? 'N/A' }}</span></div>
    <div class="detail-row"><span class="detail-label">Sport:</span><span class="detail-value">{{ $sport->name ?? 'N/A' }}</span></div>
    <div class="detail-row"><span class="detail-label">Team Name:</span><span class="detail-value">{{ $rental->team_name_with_age_group ?? 'N/A' }}</span></div>
</div>
@if(!empty($rental->items) && is_array($rental->items))
<div class="items-section">
    <h3 class="section-title">Rented Items</h3>
    @foreach($rental->items as $item)
        @if(is_array($item) && isset($item['item_id']) && isset($item['quantity']))
        <div class="item-row">
            <span>{{ $itemNames[$item['item_id']] ?? ('Item #' . $item['item_id']) }}</span>
            <span>Qty: {{ $item['quantity'] }}</span>
        </div>
        @endif
    @endforeach
@endif
@if(!empty($rental->bundles) && is_array($rental->bundles))
<div class="items-section">
    <h3 class="section-title">Rented Bundles</h3>
    @foreach($rental->bundles as $bundle)
        @if(is_array($bundle) && isset($bundle['bundle_id']))
        <div class="item-row">
            <span>{{ $bundleNames[$bundle['bundle_id']] ?? ('Bundle #' . $bundle['bundle_id']) }}</span>
            <span>Qty: {{ $bundle['quantity'] ?? 1 }}</span>
        </div>
        @elseif(is_numeric($bundle))
        <div class="item-row">
            <span>{{ $bundleNames[$bundle] ?? ('Bundle #' . $bundle) }}</span>
            <span>Qty: 1</span>
        </div>
        @endif
    @endforeach
@endif
<div class="total-section">
    <div class="total-amount">Total Amount: ${{ number_format($rental->total_amount ?? 0, 2) }}</div>
    @if($rental->insurance_option)
    <div style="margin-top:10px;font-size:16px;">Insurance: ${{ number_format($rental->insurance_option, 2) }}</div>
    @endif
    @if($rental->damage_waiver)
    <div style="margin-top:5px;font-size:16px;">Damage Waiver: ${{ number_format($rental->damage_waiver, 2) }}</div>
    @endif
</div>


