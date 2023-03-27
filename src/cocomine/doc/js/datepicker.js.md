datepicker javascript library v1.1.5
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


禁用個別日期
---
使用方法是在HTMLelement上面設置`datepicker.disableDate`屬性

1. 在date-picker上面設置`id`屬性
   ```html
   <div class="date-picker" id="ccc">
      <input type="date" class="date-picker-toggle">
   </div>
   ```
   
2. javascript選擇該input element, 可以使用jquery方法或者原生方法
   ```javascript
   //原生
   document.getElementById('ccc')
   
   //jquery
   $('#ccc')
   ```

3. 設置`disableDate`屬性, 屬性類型是`string[]`
   ```javascript
   //原生
   document.getElementById('ccc').datepicker.disableDate = []
   
   //jquery
   $('#ccc')[0].datepicker.disableDate = []
   ```

4. 設置要禁用日期(`MM-dd-yyyy`),
   ```javascript
   //原生
   document.getElementById('ccc').datepicker.disableDate = ["01-02-2023"]
   
   //jquery
   $('#ccc')[0].datepicker.disableDate = ["01-02-2023"]
   ```

5. 要禁用更多日期, 則在array上增加數值
   ```javascript
   //原生
   document.getElementById('ccc').datepicker.disableDate = ["01-02-2023", "01-03-2023"]
   
   //jquery
   $('#ccc')[0].datepicker.disableDate = ["01-02-2023", "01-03-2023"]
   ```
   
> 如發現設置後仍沒有反應, 請嘗試之後使用`drawDatePicker()`function
> ```javascript
> //原生
> document.getElementById('ccc').datepicker.drawDatePicker()
> 
> //jquery
> $('#ccc')[0].datepicker.draw()
> ```

Event 事件
---
根據用戶操作會有不同的事件

1. `datepicker.prev_month` 當用戶點擊上個月按鈕時觸發

   data.prevDate: 觸發前的日期<br>
   data.newDate: 觸發後的日期
   ```javascript
    //jquery
    $('#ccc').on('datepicker.prev_month', function(e, data){
        //do something
    })
   ```
   
2. `datepicker.next_month` 當用戶點擊下個月按鈕時觸發

    data.prevDate: 觸發前的日期<br>
    data.newDate: 觸發後的日期
    ```javascript
     //jquery
     $('#ccc').on('datepicker.next_month', function(e, data){
          //do something
     })
    ```
   
3. `datepicker.select_date` 當用戶點擊日期時觸發

    data.prevSelect: 觸發前的選擇<br>
    data.newSelect: 觸發後的選擇
    ```javascript
     //jquery
     $('#ccc').on('datepicker.select_date', function(e, data){
          //do something
     })
    ```
   
4. `datepicker.select_today` 當用戶點擊Today按鈕時觸發

    data.prevSelect: 觸發前的選擇<br>
    data.newSelect: 觸發後的選擇
    ```javascript
     //jquery
     $('#ccc').on('datepicker.select_today', function(e, data){
          //do something
     })
    ```