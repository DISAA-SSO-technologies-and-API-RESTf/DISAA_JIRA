function changeLanguageRegister() {
    var pathname = window.location.pathname;
    var URLsearch = window.location.search;
    var url_completa = 'localhost:8000' + pathname;
    if (pathname == '/en_US/register')
        url_completa = 'http://localhost:8000' + '/es_ES/register' + URLsearch;
    else
        url_completa = 'http://localhost:8000' + '/en_US/register' + URLsearch;
    //alert(url_completa);
    window.location = url_completa;
}
