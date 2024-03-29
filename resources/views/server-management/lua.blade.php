@extends('server-management.layout')
@section('title')
    Управление серверами | Lua
@endsection
@section('secondary_content')
<textarea style="overflow:auto;resize:none" class="w-100" rows="25" readonly="" wrap="off" id="console"></textarea>
<form class="g-3" name="formlua" onsubmit="sendLUA({{$server->id}}, document.formlua.command.value); return false;">
    @csrf
    <input type="hidden" name="id" value="{{$server->id}}">
    <input type="hidden" name="type" value='lua'>
    <textarea style="overflow:auto;resize:none" class="w-100" rows="25" wrap="off" name="command"></textarea>
    <div class="col-auto">
      <button type="button" class="btn btn-primary mb-3" onclick="sendLUA({{$server->id}}, document.formlua.command.value); return false;">Выполнить</button>
    </div>
</form>
@push('scripts')
<script type="text/javascript">
  let tell = 0;
  let id = '{{$server->id}}'
  function updateConsole() {
      const elem = document.getElementById('console');
      let xhr = new XMLHttpRequest();
      xhr.open('GET', '/server-management/console-update?tell='+tell+'&id='+id);
      xhr.send();
      xhr.onload = function() 
      {
        if (xhr.status != 200) { 
            console.log(`Ошибка ${xhr.status}: ${xhr.statusText}`);
        } else {
            let obj = JSON.parse(xhr.response);
            let needscroll = elem.scrollTop+elem.clientHeight == elem.scrollHeight
            if (tell > obj.tell)
            {
              elem.value = obj.rows;
            }
            else
            {
              elem.value += obj.rows;
            }
            tell = obj.tell;
            if (needscroll)
            {
              elem.scrollTo(0, elem.scrollHeight);
            }
        }
      };

      xhr.onerror = function() {
          console.log("Запрос не удался");
      };
  }

  setInterval(updateConsole, 2000);

  function sendLUA(server_id, command)
  {
    var formData = new FormData(document.formlua);

    let xhr = new XMLHttpRequest();
    xhr.open('POST', '/server-management/console-runcommand');
    xhr.send(formData);
    xhr.onload = function() 
    {
      if (xhr.status != 200) { 
          console.log(`Ошибка ${xhr.status}: ${xhr.statusText}`);
      }
      else
      {
        document.formlua.command.value = '';
      }
    };
  }
</script>
@endpush
@endsection