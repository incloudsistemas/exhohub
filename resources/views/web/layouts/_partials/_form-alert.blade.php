@if (isset(Session::get('response')['success']))
    <div class="style-msg successmsg">
        <div class="sb-msg">
            <i class="bi-hand-thumbs-up"></i>
            <strong>Muito Bem!</strong> <br />
            <span class="text-smaller ls-1">
                {{ Session::get('response')['message'] }}
            </span>
        </div>
    </div>
@endif

@if ($errors->any('error-msg-content'))
    <div class="style-msg2 errormsg">
        <div class="msgtitle">
            <i class="bi-exclamation-diamond-fill"></i>
            Você possui alguns erros no preenchimento, confira abaixo e tente novamente!
        </div>
        <div class="sb-msg">
            <ul>
                @foreach ($errors->all() as $error)
                    <li class="text-smaller ls-1">
                        {{ $error }}
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
