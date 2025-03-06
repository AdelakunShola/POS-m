<!DOCTYPE html>
<html lang="en">
<head>
<meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thermal Label Print</title>
    <script src="{{ versionedAsset('custom/libraries/barcode-lib/bwip-js-min.js') }}"></script>
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
        }
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .label-container {
            display: flex;
            page-break-after: always;
        }
    
        .label {
    display: flex;
    flex-direction: column;
    align-items: center; /* Centers content */
    justify-content: flex-start; /* Aligns everything to the top */
    padding: 2mm;
}


        .company-name {
            font-weight: bold;
            text-align: center;
            margin: 0;
        }
        .item-name {
            text-align: center;
            font-weight: bold;
            margin: 0;
        }
        .price {
            font-weight: bold;
            text-align: center;
            margin: 0;
        }
        .main-barcode-container {
            text-align: center;
            margin-top: 5px;
        }
        .barcode-number {
            text-align: center;
            margin: 0;
        }
    </style>
</head>
<body>
    
<div id="labels-container"></div>
    
    <script>
        var labelData;
        var barcodeType= "code128";

        function createLabel(data, index, size) {
            const label = document.createElement('div');
            label.className = 'label';
            label.innerHTML = `
         
                <div class="item-name">${data.itemName}</div>
                <div class="main-barcode-container">
                    <canvas id="main-barcode-${index}" style="width: ${size.barcodeWidth}; height: ${size.barcodeHeight};"></canvas>
                    <div class="barcode-number">${data.barcode}</div>
                </div>
            `;
            return label;
        }

        function updateLabels(sizeSelect) {
            const container = document.getElementById('labels-container');
            container.innerHTML = '';
            const [numLabelsPerRow, dimensions] = sizeSelect.split('_');
            const [width, height] = dimensions.split('x');

            const sizes = {
                '100x50': { labelWidth: '100mm', labelHeight: '50mm', companyNameSize: '14pt', itemNameSize: '12pt', priceSize: '16pt', barcodeWidth: '80mm', barcodeHeight: '15mm', barcodeNumberSize: '8pt' },
                '50x25': { labelWidth: '50mm', labelHeight: '25mm', companyNameSize: '10pt', itemNameSize: '8pt', priceSize: '12pt', barcodeWidth: '40mm', barcodeHeight: '7mm', barcodeNumberSize: '6pt' },
                '38x25': { labelWidth: '38mm', labelHeight: '25mm', companyNameSize: '8pt', itemNameSize: '7pt', priceSize: '10pt', barcodeWidth: '30mm', barcodeHeight: '7mm', barcodeNumberSize: '5pt' }
            };

            const size = sizes[`${width}x${height}`];

            const style = document.createElement('style');
            style.textContent = `
                @page { size: ${numLabelsPerRow === '2' ? width * 2 : width}mm ${height}mm; margin: 0; }
                .label-container { width: ${numLabelsPerRow === '2' ? width * 2 : width}mm; height: ${height}mm; }
                .label { width: ${width}mm; height: ${height}mm; }
                .company-name { font-size: ${size.companyNameSize}; }
                .item-name { font-size: ${size.itemNameSize}; }
                .price { font-size: ${size.priceSize}; }
                .barcode-number { font-size: ${size.barcodeNumberSize}; }
            `;
            document.head.appendChild(style);

            let labelIndex = 0;
            labelData.forEach((data) => {
                for (let i = 0; i < data.quantity; i++) {
                    if (labelIndex % numLabelsPerRow === 0) {
                        const labelContainer = document.createElement('div');
                        labelContainer.className = 'label-container';
                        container.appendChild(labelContainer);
                    }

                    const label = createLabel(data, labelIndex, size);
                    container.lastChild.appendChild(label);

                    labelIndex++;
                }
            });

            const barcodes = Array.from(document.querySelectorAll('[id^="main-barcode-"]'));
            barcodes.forEach((canvas, index) => {
                const dataIndex = labelData.findIndex(item => 
                    index >= labelData.slice(0, labelData.indexOf(item)).reduce((sum, curr) => sum + curr.quantity, 0) &&
                    index < labelData.slice(0, labelData.indexOf(item) + 1).reduce((sum, curr) => sum + curr.quantity, 0)
                );

                console.log("Generating barcode for:", labelData[dataIndex]?.barcode);
                
                bwipjs.toCanvas(canvas.id, {
                    bcid: barcodeType,
                    text: String(labelData[dataIndex]?.barcode),
                    scale: 1,
                    height: parseInt(size.barcodeHeight),
                    includetext: false,
                    textxalign: 'center',
                });
            });
        }

        window.addEventListener('message', function(event) {
            if (event.data === 'print') {
                window.print();
                return;
            }

            const data = event.data;
            barcodeType = data.barcode_type;
            var size = data.size;
            labelData = JSON.parse(data.itemData);

            const container = document.getElementById('labels-container');
            container.innerHTML = '';

            updateLabels(size);
        });

    </script>


<script>
    window.addEventListener('message', async function(event) {
        if (event.data === 'print') {
            try {
                if (window.labelData && window.labelData.length > 0) {
                    console.log("Preparing to save barcode data:", window.labelData);

                    const response = await fetch('/save-barcodes', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ barcodes: window.labelData })
                    });

                    const responseData = await response.json();
                    console.log("Server response:", responseData);

                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }

                    console.log("Barcode data saved successfully.");
                } else {
                    console.warn("No barcode data available to save.");
                }

                console.log("Saving complete, now printing...");
                window.print();
            } catch (error) {
                console.error("Failed to save barcode data:", error);
            }
        } else {
            // Handle barcode generation
            const data = event.data;
            barcodeType = data.barcode_type;
            var size = data.size;
            labelData = JSON.parse(data.itemData);

            const container = document.getElementById('labels-container');
            container.innerHTML = '';

            updateLabels(size);
        }
    });
</script>

</body>
</html>