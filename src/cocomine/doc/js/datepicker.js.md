datepicker javascript library
===

需要載入css檔案
```html
<link rel="stylesheet" href="/assets/css/myself/datetimepicker.css">
```

用法
---
   
1. 誰是你需要一個`input` element type 填`date`, class 加上 `date-picker-toggle`
    ```html
    <input type="date" class="date-picker-toggle">
    ```
   
2. 包一層`div`, class 加上 `date-picker`
    ```html
    <div class="date-picker">
         <input type="date" class="date-picker-toggle">
    </div>
    ```
    > 如果想使用嵌入式則 class 加上 `date-picker-inline`, <br>
   > 和在`input` element上方或者下方加上`<div class="date-calendar"></div>`
    >```html
    ><div class="date-picker date-picker-inline">
    >   <input type="date" class="date-picker-toggle">
    >   <div class="date-calendar"></div>
    ></div>
    >```

3. 可以根據需要填上其他屬性`min` `max`
    ```html
    <div class="date-picker">
         <input type="date" class="date-picker-toggle" min="" max="">
    </div>
    ```