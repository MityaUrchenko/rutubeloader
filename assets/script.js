"use strict";

document.addEventListener("DOMContentLoaded", function(event) {

  let fileForm = document.getElementById("fileForm");
  let testFileLink = document.getElementById("testFileLink");
  let manual = document.getElementById("manual");
  fileForm.onsubmit = async (e) => {
    e.preventDefault();

    let response = await fetch('/api/parse.php', {
      method: 'POST',
      body: new FormData(fileForm)
    });

    let result = await response.json();

    fileForm.classList.add("d-none");
    testFileLink.classList.add("d-none");
    manual.classList.add("d-none");

    showRows(result);
    sendData(result);
  };


  let showRows = (arr) => {
    let table = document.getElementById("dataTable");

    let tbody = document.createElement("tbody");
    for (let key in arr){
      let tr = document.createElement("tr");
      tr.setAttribute('id', 'video_'+key);


      let row = arr[key];
      for (let cellKey in row){
        if(cellKey == 3) continue;
        let td = document.createElement("td");
        let div = document.createElement("div");
        div.classList.add("d-flex");
        div.append(row[cellKey] || "");
        td.append(div);

        if(cellKey == 0){
          let small = document.createElement("small");
          small.classList.add("d-flex","mt-2");
          small.append(row[3] || "");
          td.append(small);
        }

        tr.append(td);
      }

/*
      for (let cell of arr[key]){
        let td = document.createElement("td");
        let div = document.createElement("div");
        div.classList.add("d-flex");
        div.append(cell || "");
        td.append(div);
        tr.append(td);
      }
*/

      let td = document.createElement("td");
      let spinner = document.createElement("span");
      spinner.classList.add("spinner-border","spinner-border-sm","text-primary");


      let div = document.createElement("div");
      div.classList.add("video_status","d-flex","justify-content-end");
      div.innerText = "sent";

      td.append(spinner);
      td.append(div);
      tr.append(td);


      tbody.append(tr);
    }
    table.append(tbody);
    table.classList.remove("d-none");
  }

  let sendData = (arr) => {
    for (let key in arr){
      fetch('/api/upload.php', {
        method: 'POST',
        mode: 'same-origin',
        credentials: 'include',
        headers: {
          'Accept': 'application/json, text/plain, */*; charset=UTF-8',
          //'Content-Type': 'application/json; charset=UTF-8',
          "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8" // через multiform/data $_POST пустой
        },
        body: "video=" + JSON.stringify(arr[key]),
      })
      .then(res => res.json())
      .then(res => {

        let videoRow = document.getElementById("video_"+key);
        videoRow.setAttribute('id', res.video_id);

        checkVideo(res);
      });
    }
  }

  let checkVideo = (data) => {
    let request = (id) => {
      fetch('/api/checkVideo.php', {
        method: 'POST',
        mode: 'same-origin',
        credentials: 'include',
        headers: {
          'Accept': 'application/json, text/plain, */*; charset=UTF-8',
          //'Content-Type': 'application/json; charset=UTF-8',
          "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8" // через multiform/data $_POST пустой
        },
        body: "id=" + JSON.stringify(id),
      })
      .then(res => res.json())
      .then(res => {
        console.log(res);
        let videoRow = document.getElementById(id);
        let statusBlock = videoRow.getElementsByClassName('video_status');
        let spinner = videoRow.getElementsByClassName('spinner-border');
        let status = "";
        if(res.hasOwnProperty('id')){
          status = res.action_reason.name;
          if(res.action_reason.id == 0){
            status = "Loaded";
            spinner[0].remove();
            clearTimeout(timerId);
            addVideoToList(res);
          }
        }else{
          status = res.detail;
          clearTimeout(timerId);
        }
        statusBlock[0].innerText = status;
      });
    }
    let timerId = setInterval(() => request(data.video_id), 1000*10);
  }

  let addVideoToList = (res) => {
    let col = document.createElement("div");
    col.classList.add("col-3", "d-flex", "mb-4");


    let card = document.createElement("div");
    card.classList.add("card");

    let src = res.thumbnail_url;
    let img = document.createElement("img");
    img.src = src;
    img.classList.add("w-100", "card-img-top");
    card.append(img);

    let body = document.createElement("div");
    body.classList.add("card-body", "d-flex", "flex-column");

    let title = document.createElement("h5");
    title.classList.add("card-title");
    title.innerText = res.title;
    body.append(title);

    let description = document.createElement("div");
    description.classList.add("card-text", "mb-3");
    description.innerText = res.description;
    body.append(description);

    let link = document.createElement("a");
    link.classList.add("btn", "btn-primary", "mt-auto");
    link.setAttribute("href", res.video_url);
    link.innerText = "Открыть";
    body.append(link);

    card.append(body);
    col.append(card);

    videoList.prepend(col);
  }

});