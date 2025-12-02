<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Top Sheet</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        h2 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table, th, td { border: 1px solid #000; }
        th, td { padding: 8px; text-align: left; }
        .barcode { text-align: center; margin-top: 20px; }
    </style>
</head>
<body>
    <h2>Top Sheet</h2>

    <p><strong>Case Type:</strong> {{ $case->case_type }}</p>
    <p><strong>Subject:</strong> {{ $case->subject }}</p>
    <p><strong>Lawyer:</strong> {{ $case->lawyer->full_name }}</p>

    <h4>Petitioners</h4>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Address</th>
                <th>Phone</th>
                <th>Email</th>
                <th>NID</th>
            </tr>
        </thead>
        <tbody>
            @foreach($case->petitioners as $p)
                <tr>
                    <td>{{ $p->name }}</td>
                    <td>{{ $p->address }}</td>
                    <td>{{ $p->phone }}</td>
                    <td>{{ $p->email }}</td>
                    <td>{{ $p->nid }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h4>Respondents</h4>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Designation</th>
                <th>Organization</th>
                <th>Address</th>
            </tr>
        </thead>
        <tbody>
            @foreach($case->respondents as $r)
                <tr>
                    <td>{{ $r->name }}</td>
                    <td>{{ $r->designation }}</td>
                    <td>{{ $r->organization }}</td>
                    <td>{{ $r->address }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="barcode">
        <img src="data:image/png;base64,{{ $barcode }}" alt="Case Barcode">
        <p>{{ $case->temporary_barcode }}</p>
    </div>
</body>
</html>
