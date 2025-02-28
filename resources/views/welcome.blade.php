<?php
// Backend Code
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['url'])) {
    $apiUrl = $_POST['url'];
    
    
    $data = fetchDataFromApi($apiUrl);
    
    if (!is_array($data)) {
        $data = [$data];
    }

    echo json_encode($data);
    exit;
}

function fetchDataFromApi($url) {
    if (filter_var($url, FILTER_VALIDATE_URL)) {
        $response = file_get_contents($url);
        
        if ($response === FALSE) {
            return ['error' => 'Failed to fetch data from API.'];
        }

        $data = json_decode($response, true);
        error_log("Fetched API Data: " . print_r($data, true)); 

        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['error' => 'Failed to decode JSON response from the API.'];
        }

        return $data;
    } else {
        return ['error' => 'Invalid API URL.'];
    }
}
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel Drag & Drop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
    body { font-family: Arial, sans-serif; text-align: center; }
    .container { max-width: 900px; margin: auto; }
    .input, .button, .search-input { margin: 10px; padding: 5px; }
    .columns { display: flex; gap: 10px; justify-content: center; }
    .column { flex: 1; background: #f4f4f4; padding: 10px; min-height: 300px; border-radius: 5px; }
    .list { min-height: 100px; background: white; padding: 5px; border: 1px solid #ddd; border-radius: 5px; list-style-type: none; }
    .item { padding: 8px; background: #ddd; margin-bottom: 5px; cursor: grab; border-radius: 3px; }
</style>
<body>

<div class="container">
    <h2>Laravel Drag & Drop Sistēma</h2>

    <!-- API URL Input -->
    <input type="text" id="apiUrl" placeholder="Ievadi API URL" class="input"/>
    <button id="loadData" class="button">Ielādēt</button>

    <!-- Kolonnu skaits -->
    <label for="columnCount">Kolonnu skaits:</label>
    <select id="columnCount">
        <option value="1" selected>1 kolonnas</option>
        <option value="2">2 kolonnas</option>
        <option value="3">3 kolonnas</option>
        <option value="4">4 kolonnas</option>
        <option value="5">5 kolonnas</option>
    </select>

    <div class="columns" id="columns">
        <!-- Pirmā kolonna ar API datiem -->
        <div class="column">
            <input type="text" class="search-input" placeholder="Meklēt..." onkeyup="searchItems(this, 0)">
            <ul id="column-0" class="list"></ul>
        </div>
        <!-- Papildus kolonnas -->
    </div>
</div>




<script>
$(document).ready(function () {
    let columnCount = 1;
    preserveAndInitializeColumns(columnCount);

    $('#columnCount').change(function () {
        columnCount = $(this).val();
        preserveAndInitializeColumns(columnCount);
    });

    $('#loadData').click(function () {
        loadData();
    });

    function loadData() {
        let apiUrl = $('#apiUrl').val();
        if (!apiUrl) {
            alert('Lūdzu, ievadiet API URL!');
            return;
        }

        console.log('Sūtam pieprasījumu uz API URL:', apiUrl);

        $.ajax({
            url: '/fetch-api',
            type: 'POST',
            data: { url: apiUrl },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                let filteredData = response.map(item => {
                    return {
                        id: item.id,
                        name: item.name
                    };
                });

                if (response.error) {
                    alert(response.error);
                    return;
                }

                if (!Array.isArray(response)) {
                    console.error('Nederīgs datu formāts: Sagaidīts masīvs, saņemts', typeof response, response);
                    alert('Saņemts nederīgs datu formāts!');
                    return;
                }

                let allItems = []; 
                $('.list .item').each(function() {
                    allItems.push($(this).data('id'));
                });

                response.forEach(item => {
                    if (item.id && item.name && !allItems.includes(item.id)) { 
                        $('#column-0').append(`<li class="item" data-id="${item.id}">${item.name}</li>`);
                    } else if (!item.id || !item.name) {
                        console.warn('Izlaižam vienību, jo trūkst "id" vai "name" lauka:', item);
                    }
                });

                initializeDragAndDrop();
            },
            error: function () {
                alert('Neizdevās ielādēt datus!');
            }
        });
    }

    function preserveAndInitializeColumns(count) {
        let existingItems = [];
        $('.list').each(function () {
            existingItems.push($(this).children('.item').toArray());
        });

        $('#columns').empty();
        for (let i = 0; i < count; i++) {
            let columnHtml = `
                <div class="column">
                    <input type="text" class="search-input" placeholder="Meklēt..." onkeyup="searchItems(this, ${i})">
                    <ul id="column-${i}" class="list"></ul>
                </div>`;
            $('#columns').append(columnHtml);
        }

        let itemIndex = 0;
        let totalItems = existingItems.flat().length;
        let itemsPerColumn = Math.ceil(totalItems / count);

        for (let i = 0; i < count; i++) {
            for (let j = 0; j < itemsPerColumn; j++) {
                if (existingItems.flat()[itemIndex]) {
                    $('#column-' + i).append(existingItems.flat()[itemIndex]);
                    itemIndex++;
                }
            }
        }

        initializeDragAndDrop();
    }

    function initializeDragAndDrop() {
        $(".list").sortable({
            connectWith: ".list",
            placeholder: "ui-state-highlight"
        }).disableSelection();
    }

    window.searchItems = function (input, columnIndex) {
        let searchText = $(input).val().toLowerCase().replace(/\s/g, '');
        $(`#column-${columnIndex} .item`).each(function () {
            let text = $(this).text().toLowerCase().replace(/\s/g, '');
            $(this).toggle(text.includes(searchText));
        });
    };
});
</script>



</body>
</html> 