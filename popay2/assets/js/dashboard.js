        $(document).ready(function() {
            const transactionDetails = document.getElementById('transaction-details');
            const noTransaction = document.getElementById('no-transaction');
            const subtotal = document.getElementById('subtotal');
            const total = document.getElementById('total');
            const retailBtn = document.getElementById('retail-btn');
            const bulkBtn = document.getElementById('bulk-btn');
            const addDetailsBtn = document.getElementById('add-details');
            const priceInput = document.getElementById('price-input');
            const emptyCylinder = document.getElementById('empty-cylinder');
            const fillWeight = document.getElementById('fill-weight');
            const transactionDate = document.getElementById('transaction-date');
            const customer = document.getElementById('customer');
            const saveBtn = document.getElementById('save-btn');

            var fillname = document.getElementById('fill-name');
            var total2 = 0;
            var order = [];
            transactionDate.innerText = new Date().toLocaleString();
            let currentPrice = <?php echo $set['retailPrice'] ?>;
            priceInput.value = `₦${currentPrice}`;

            retailBtn.addEventListener('click', () => {
                currentPrice = <?php echo $set['retailPrice'] ?>;
                priceInput.value = `₦${currentPrice}`;
            });

            bulkBtn.addEventListener('click', () => {
                currentPrice = <?php echo $set['bulkPrice'] ?>;
                priceInput.value = `₦${currentPrice}`;
            });

            addDetailsBtn.addEventListener('click', () => {
                const empty = emptyCylinder.value;
                const fill = fillWeight.value;
                customer.innerText = fillname.value;
                if (empty && fill && !isNaN(empty) && !isNaN(fill) && fill > 0) {
                    noTransaction.style.display = 'none';
                    const item = document.createElement('p');
                    const weightToFill = parseFloat(fill);
                    const cost = weightToFill * currentPrice;
                    
                    item.innerHTML = `
    <strong>
        Gas Type: <span style='float: right;'>${currentPrice === <?php echo json_encode($set['retailPrice']); ?> ? "Retail" : "Bulk"}</span><br>
        Empty Cylinder: <span style='float: right;'>${empty}kg</span><br>
        Quantity Fill: <span style='float: right;'>${fill}kg</span><br>
        Price per Kg: <span style='float: right;'>₦${currentPrice}</span><br>
        Total Cost: <span style='float: right;'>₦${cost}</span>
    </strong>
`;

transactionDetails.appendChild(item);
total2 += cost;
subtotal.textContent = `₦${total2}`;
total.textContent = `₦${total2}`;
order.push(`${empty},${fill},${cost},${currentPrice}`);

                } else {
                    alert('Please fill in all details!');
                }
                console.log(order);
            });

            document.getElementById('pay-btn').addEventListener('click', () => {
                const noTransactionMessage = '<p id="no-transaction" class="text-muted">No items added yet.</p>';
                
                if (transactionDetails.innerHTML.trim() === noTransactionMessage) {
                    alert('Empty item, select kg');
                } else {
                    const selectedMethod = document.querySelector('input[name="paymentMethod"]:checked').value;
                    const printContent = `
                        <div id="print">
                            <div class="receipt-header">
                                <h2>${<?php echo json_encode($set['companyName']); ?>}</h2>
                                <h5>${<?php echo json_encode($set['Address']); ?>}</h5>
                                <h5>${<?php echo json_encode($set['Phone']); ?>}</h5>
                                <p>Date: ${new Date().toLocaleString()}</p>
                            </div>
                            <div class="receipt-body">
                                <p>Customer: ${document.getElementById('fill-name').value}</p>
                                ${transactionDetails.innerHTML}
                                <div class="receipt-details">
                                    <p>Tax: ₦0.00</p>
                                    <p class="total">Total: ₦${document.getElementById('total').innerText}</p>
                                </div>
                            </div>
                            <div class="receipt-footer">
                                <p>Paid with: ${selectedMethod}</p>
                                <p>Thank you for your patronage!<br>Please come back again</p>
                            </div>
                        </div>
                    `;
                    for (let i = 0; i < order.length; i++) {
                        const data = new FormData();
                        data.append('order', order[i]);
                        data.append('customer', customer.innerText);
                        data.append('total2', total2);
                        data.append('selectedMethod', selectedMethod);

                        const xhr = new XMLHttpRequest();
                        xhr.open('POST', 'order.php', true);

                        xhr.onload = function () {
                            if (xhr.status === 200) {
                                console.log('Success:', xhr.responseText);
                            } else {
                                console.error('Error:', xhr.statusText);
                            }
                        };

                        xhr.onerror = function () {
                            console.error('Error: AJAX request failed.');
                        };

                        xhr.send(data);
                    }

                    const printWindow = window.open('', '_blank', 'width=800,height=600');
                    printWindow.document.open();
                    printWindow.document.write(`
                        <html>
                            <head>
                                <title>Receipt</title>
                                <style>
                                    @media print {
                                        body {
                                            margin: 0;
                                            font-size: 12px;
                                        }
                                        #print {
                                            width: 80mm;
                                            padding: 5mm;
                                            margin: auto;
                                            font-family: 'Courier New', Courier, monospace;
                                        }
                                        .receipt-header, .receipt-footer {
                                            text-align: center;
                                        }
                                        .receipt-details, .receipt-footer {
                                            border-top: 1px dashed #000;
                                            margin-top: 10px;
                                            padding-top: 5px;
                                        }
                                        .total {
                                            font-weight: bold;
                                            text-align: right;
                                        }
                                    }
                                </style>
                            </head>
                            <body onload="window.print(); window.close();">
                                ${printContent}
                            </body>
                        </html>
                    `);
                    printWindow.document.close();
                    transactionDetails.innerHTML = noTransactionMessage;
                }
            });
        });