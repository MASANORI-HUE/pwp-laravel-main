@auth
<div class="p-4">
    <form action="{{ route('tweet.create') }}" method="post" enctype="multipart/form-data">
        @csrf
        <x-form.textarea name="tweet" rows="3" placeholder="つぶやきを入力" helpText="140文字まで" required />
        @feature('image.upload')
            <x-tweet.form.images />
        @else
        <p class="text-sm text-gray-500 mt-2">
            画像アップロード機能は準備中です。次の項で公開方法を確認しましょう。
        </p>
        @endfeature
        <div class="flex flex-wrap justify-end mt-4">
            <x-element.button>
                つぶやく
            </x-element.button>
        </div>
    </form>
</div>
@endauth
@guest
    <div class="flex flex-wrap justify-center">
        <div class="w-1/2 p-4 flex-wrap justify-evenly">
            <x-element.button-a :href="route('login')">ログイン</x-element.button-a>
            <x-element.button-a :href="route('register')" theme="secondary">ユーザー登録</x-element.button-a>
        </div>
    </div>
@endguest