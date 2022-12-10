<div class="row">
    <div class="col-md-12">
        <div class="table-wrap">
            <table class="table table-bordered table-dark table-hover">
                <thead>
                <tr>
                    @foreach($data['header'] as $value)
                    <th>{{$value}}</th>
                    @endforeach
                </tr>
                </thead>
                <tbody>
                    @foreach($data['data'] as $value)
                        <tr>
                            @foreach($value as $v)
                                <td>{{$v}}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>