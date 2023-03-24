如何使用 fetch AJAX
===
格式基本不變留意要改的位置
```javascript
fetch(/*Here type url*/url, {
    method: 'POST',
    redirect: 'error',
    headers: {
        'Content-Type': 'application/json; charset=UTF-8',
        'X-Requested-With': 'XMLHttpRequest'
    },
    body: JSON.stringify({/* Here type data */})
}).then(async (response) => {
    const json = await response.json();
    //do something with json
}).catch((error) => {
    console.log(error);
});
```
詳情請參閱[官方文檔](https://developer.mozilla.org/en-US/docs/Web/API/Fetch_API/Using_Fetch)