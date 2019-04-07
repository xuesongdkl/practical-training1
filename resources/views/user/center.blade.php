<h3>Center</h3>

<input type="hidden" id="token" value="{{$token}}">
<input type="hidden" id="id" value="{{$uid}}">



<script src="{{URL::asset('/js/jquery-1.12.4.min.js')}}"></script>
<script>
    $(function(){
//        alert(111);
        var token = $('#token').val();
        var uid = $('#id').val();
        var info = function(){
            $.post(
                'http://xuesong.shansister.com/center1',
                {token:token,uid:uid},
                function(data){
                    alert(data)
                    /*if(data==2){
                        alert('此账号已有其他用户登录');
                        location.href='/userlogin';
                    }*/
                }
            );
        };
        var s = setInterval(function(){
            info();
        },1000*3)
    })
</script>

