<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Ticket - {{ $booking->booking_reference }}</title>
    <style>
        /* Reset & Base */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            color: #1E293B;
            background: #f8fafc;
        }

        /* Ticket Container */
        .ticket {
            width: 100%;
            max-width: 700px;
            margin: 20px auto;
            background: #ffffff;
            border: 2px solid #1E293B;
            border-radius: 12px;
            overflow: hidden;
        }

        /* Header */
        .ticket-header {
            background: #1E293B;
            color: #ffffff;
            padding: 20px 30px;
            display: table;
            width: 100%;
        }

        .header-left {
            display: table-cell;
            vertical-align: middle;
            width: 60%;
        }

        .header-right {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
            width: 40%;
        }

        .logo-text {
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 2px;
            color: #FFC107;
        }

        .logo-tagline {
            font-size: 10px;
            color: #94A3B8;
            margin-top: 4px;
        }

        .ticket-type {
            background: #FFC107;
            color: #1E293B;
            font-size: 11px;
            font-weight: bold;
            padding: 6px 16px;
            border-radius: 20px;
            display: inline-block;
        }

        /* Body */
        .ticket-body {
            padding: 25px 30px;
            display: table;
            width: 100%;
        }

        .body-left {
            display: table-cell;
            vertical-align: top;
            width: 65%;
            padding-right: 20px;
            border-right: 2px dashed #E2E8F0;
        }

        .body-right {
            display: table-cell;
            vertical-align: top;
            width: 35%;
            padding-left: 20px;
            text-align: center;
        }

        /* Passenger Info */
        .passenger-name {
            font-size: 18px;
            font-weight: bold;
            color: #1E293B;
            margin-bottom: 15px;
            text-transform: uppercase;
        }

        /* Route Section */
        .route-section {
            margin-bottom: 20px;
        }

        .route-row {
            display: table;
            width: 100%;
            margin-bottom: 5px;
        }

        .route-point {
            display: table-cell;
            width: 45%;
        }

        .route-arrow {
            display: table-cell;
            width: 10%;
            text-align: center;
            vertical-align: middle;
            font-size: 18px;
            color: #FFC107;
        }

        .route-label {
            font-size: 10px;
            color: #64748B;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .route-value {
            font-size: 16px;
            font-weight: bold;
            color: #1E293B;
        }

        /* Details Grid */
        .details-grid {
            display: table;
            width: 100%;
        }

        .detail-row {
            display: table-row;
        }

        .detail-item {
            display: table-cell;
            padding: 8px 0;
            width: 50%;
        }

        .detail-label {
            font-size: 10px;
            color: #64748B;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .detail-value {
            font-size: 14px;
            font-weight: bold;
            color: #1E293B;
        }

        /* Price Tag */
        .price-section {
            background: #FFF8E1;
            border: 2px solid #FFC107;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .price-label {
            font-size: 10px;
            color: #64748B;
            text-transform: uppercase;
        }

        .price-value {
            font-size: 28px;
            font-weight: bold;
            color: #1E293B;
        }

        /* QR Code Placeholder */
        .qr-section {
            margin-top: 10px;
        }

        .qr-placeholder {
            width: 100px;
            height: 100px;
            background: #f1f5f9;
            border: 2px solid #E2E8F0;
            margin: 0 auto 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .qr-placeholder img {
            width: 90px;
            height: 90px;
        }

        .booking-ref {
            font-size: 14px;
            font-weight: bold;
            color: #1E293B;
            letter-spacing: 1px;
        }

        /* Footer */
        .ticket-footer {
            background: #F8FAFC;
            border-top: 1px solid #E2E8F0;
            padding: 15px 30px;
            text-align: center;
        }

        .footer-text {
            font-size: 11px;
            color: #64748B;
        }

        .footer-text strong {
            color: #1E293B;
        }

        /* Tear Line */
        .tear-line {
            position: relative;
            height: 0;
        }

        .tear-circle-left {
            position: absolute;
            left: -12px;
            top: -12px;
            width: 24px;
            height: 24px;
            background: #f8fafc;
            border-radius: 50%;
        }

        .tear-circle-right {
            position: absolute;
            right: -12px;
            top: -12px;
            width: 24px;
            height: 24px;
            background: #f8fafc;
            border-radius: 50%;
        }

        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-confirmed {
            background: #D1FAE5;
            color: #065F46;
        }

        .status-pending {
            background: #FEF3C7;
            color: #92400E;
        }

        .status-cancelled {
            background: #FEE2E2;
            color: #991B1B;
        }
    </style>
</head>

<body>
    <div class="ticket">
        {{-- Header --}}
        <div class="ticket-header">
            <div class="header-left">
                <div class="logo-text">SOUTHERN LINES</div>
                <div class="logo-tagline">Safe & Comfortable Bus Travel</div>
            </div>
            <div class="header-right">
                <span class="ticket-type">E-TICKET</span>
            </div>
        </div>

        {{-- Body --}}
        <div class="ticket-body">
            {{-- Left Column --}}
            <div class="body-left">
                {{-- Passenger Name --}}
                <div class="passenger-name">
                    {{ $booking->user ? $booking->user->name : $booking->guest_name }}
                </div>

                {{-- Route --}}
                <div class="route-section">
                    <div class="route-row">
                        <div class="route-point">
                            <div class="route-label">From</div>
                            <div class="route-value">{{ $booking->schedule->origin->city ?? 'N/A' }}</div>
                        </div>
                        <div class="route-arrow">→</div>
                        <div class="route-point">
                            <div class="route-label">To</div>
                            <div class="route-value">{{ $booking->schedule->destination->city ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>

                {{-- Details --}}
                <div class="details-grid">
                    <div class="detail-row">
                        <div class="detail-item">
                            <div class="detail-label">Date</div>
                            <div class="detail-value">{{ \Carbon\Carbon::parse($booking->schedule->departure_date)->format('M d, Y') }}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Time</div>
                            <div class="detail-value">{{ \Carbon\Carbon::parse($booking->schedule->departure_time)->format('h:i A') }}</div>
                        </div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-item">
                            <div class="detail-label">Bus</div>
                            <div class="detail-value">{{ $booking->schedule->bus->name ?? $booking->schedule->bus->code ?? 'N/A' }}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Seat(s)</div>
                            <div class="detail-value">{{ is_array($booking->seat_numbers) ? implode(', ', $booking->seat_numbers) : $booking->seat_numbers }}</div>
                        </div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-item">
                            <div class="detail-label">Passengers</div>
                            <div class="detail-value">{{ $booking->adults }} Adult{{ $booking->adults > 1 ? 's' : '' }}{{ $booking->children > 0 ? ', ' . $booking->children . ' Child' . ($booking->children > 1 ? 'ren' : '') : '' }}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Status</div>
                            <div class="detail-value">
                                <span class="status-badge status-{{ strtolower($booking->status) }}">
                                    {{ ucfirst($booking->status) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column --}}
            <div class="body-right">
                {{-- Price --}}
                <div class="price-section">
                    <div class="price-label">Total Fare</div>
                    <div class="price-value">₱{{ number_format($booking->total_price, 2) }}</div>
                </div>

                {{-- QR Code --}}
                <div class="qr-section">
                    <div class="qr-code">
                        <img src="data:image/svg+xml;base64,{{ base64_encode(QrCode::format('svg')->size(100)->color(30, 41, 59)->generate($booking->booking_reference)) }}" alt="QR Code" style="width: 100px; height: 100px;">
                    </div>
                    <div class="booking-ref">{{ $booking->booking_reference }}</div>
                </div>
            </div>
        </div>

        {{-- Tear Line --}}
        <div class="tear-line">
            <div class="tear-circle-left"></div>
            <div class="tear-circle-right"></div>
        </div>

        {{-- Footer --}}
        <div class="ticket-footer">
            <div class="footer-text">
                <strong>Keep this ticket safe.</strong> Present to conductor upon boarding.<br>
                For inquiries, call (02) 8123-4567 or email support@southernlines.ph
            </div>
        </div>
    </div>
</body>

</html>