<h3>Center</h3>

<input type="hidden" id="token" value="{{$token}}">
<input type="hidden" id="id" value="{{$uid}}">

<table>
    <tr>
        <td>ID</td>
        <td>账号</td>
        <td>添加时间</td>
        <td>是否在线</td>
    </tr>
    @foreach($data as $k => $v)
    <tr>
        <td>{{$v->uid}}</td>
        <td>{{$v->u_name}}</td>
        <td>{{date($v->add_time,'Y-m-d H:i:s')}}</td>
        <td>
            @if($v->is_online==1)
            在线
            @elseif
            未登录
            @endif
        </td>
    </tr>
</table>


<script src="{{URL::asset('/js/jquery-1.12.4.min.js')}}"></script>
<script>
    $(function(){
        var token = $('#token').val();
        var uid = $('#id').val();
        var data = function(){
            $.post(
                'http://xuesong.shansister.com/center1',
                {token:token,uid:uid},
                function(data){
                    if(data==2){
                        alert('此账号已有其他用户登录');
                        location.href='/userlogin';
                    }
                }
            );
        };
        var s = setInterval(function(){
            data();
        },1000*3)
    })
</script>

