<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Sales Performance Report - {{ $platform->name }}</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Helvetica', 'Arial', sans-serif;
      font-size: 11px;
      color: #1a1a1a;
      line-height: 1.4;
    }

    .page {
      padding: 30px 40px;
    }

    .header {
      display: table;
      width: 100%;
      margin-bottom: 20px;
    }

    .header-left {
      display: table-cell;
      vertical-align: top;
      width: 50%;
    }

    .header-right {
      display: table-cell;
      vertical-align: top;
      width: 50%;
      text-align: right;
    }

    .logo {
      height: 50px;
      margin-bottom: 8px;
    }

    .company-name {
      font-size: 10px;
      color: #555;
    }

    .doc-title {
      font-size: 20px;
      font-weight: bold;
      color: #1a365d;
      margin-bottom: 4px;
    }

    .doc-date {
      font-size: 10px;
      color: #666;
    }

    .divider {
      border: none;
      border-top: 2px solid #1a365d;
      margin: 15px 0;
    }

    .meta {
      font-size: 11px;
      color: #444;
      margin-bottom: 20px;
    }

    .meta span {
      margin-right: 20px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }

    th {
      background-color: #1a365d;
      color: #ffffff;
      font-size: 10px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      padding: 10px 12px;
      text-align: left;
    }

    th.right {
      text-align: right;
    }

    td {
      padding: 10px 12px;
      font-size: 11px;
      border-bottom: 1px solid #e5e7eb;
    }

    td.right {
      text-align: right;
    }

    tr:nth-child(even) {
      background-color: #f9fafb;
    }

    .achievement-high {
      color: #059669;
      font-weight: 600;
    }

    .achievement-mid {
      color: #d97706;
      font-weight: 600;
    }

    .achievement-low {
      color: #6b7280;
      font-weight: 600;
    }

    .footer {
      margin-top: 30px;
      padding-top: 15px;
      border-top: 1px solid #e5e7eb;
      font-size: 9px;
      color: #9ca3af;
      text-align: center;
    }
  </style>
</head>
<body>
  <div class="page">
    <div class="header">
      <div class="header-left">
        @if(file_exists($logoPath))
          <img src="{{ $logoPath }}" class="logo" alt="Logo">
        @endif
        <div class="company-name">La Sentinelle Ltd</div>
      </div>
      <div class="header-right">
        <div class="doc-title">Sales Performance Report</div>
        <div class="doc-date">{{ $now->format('d F Y') }}</div>
      </div>
    </div>

    <hr class="divider">

    <div class="meta">
      <span><strong>Platform:</strong> {{ $platform->name }}</span>
      <span><strong>Financial Year:</strong> {{ $financialYearLabel }}</span>
    </div>

    <table>
      <thead>
        <tr>
          <th>Salesperson</th>
          <th class="right">Target (MUR)</th>
          <th class="right">Sales (MUR)</th>
          <th class="right">Achievement</th>
        </tr>
      </thead>
      <tbody>
        @foreach($data as $entry)
          @php
            $achievementClass = $entry['percentage'] >= 100 ? 'achievement-high' : ($entry['percentage'] >= 75 ? 'achievement-mid' : 'achievement-low');
          @endphp
          <tr>
            <td>{{ $entry['salesperson']->first_name }} {{ $entry['salesperson']->last_name }}</td>
            <td class="right">{{ number_format($entry['target'], 2) }}</td>
            <td class="right">{{ number_format($entry['sales'], 2) }}</td>
            <td class="right {{ $achievementClass }}">{{ number_format($entry['percentage'], 1) }}%</td>
          </tr>
        @endforeach
      </tbody>
    </table>

    <div class="footer">
      Generated on {{ $now->format('d/m/Y \a\t H:i') }} &middot; Digital Bookings &middot; La Sentinelle Ltd
    </div>
  </div>
</body>
</html>
