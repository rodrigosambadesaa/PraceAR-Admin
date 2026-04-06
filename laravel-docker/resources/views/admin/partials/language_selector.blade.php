<nav class="language-selector" aria-label="Selector de idioma">
    <ul class="flags">
        @foreach ($languages as $languageCode => $languageLabel)
        @php
        $params = request()->query();
        if ($activePage === 'language') {
        $params['page'] = 'language';
        $params['codigo_idioma'] = $languageCode;
        unset($params['lang']);
        } else {
        if ($activePage !== 'index') {
        $params['page'] = $activePage;
        } elseif (isset($params['page']) && !is_numeric((string) $params['page'])) {
        unset($params['page']);
        }
        $params['lang'] = $languageCode;
        }
        $languageUrl = $rootUrl . (!empty($params) ? '?' . http_build_query($params) : '');
        @endphp
        <li>
            <a href="{{ $languageUrl }}"
                aria-label="{{ $languageLabel }}"
                tabindex="0"
                @if ($languageCode===$currentLang) aria-current="true" @endif>
                <img class="language-flag" width="15" height="15"
                    src="{{ url('/img/flags/' . $languageCode . '.png') }}"
                    alt="{{ $languageLabel }}">
            </a>
        </li>
        @endforeach
    </ul>
</nav>