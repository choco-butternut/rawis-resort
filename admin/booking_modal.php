<!-- tuhaya nalain kathlyn -->

<div id="bookingModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);z-index:3000;align-items:center;justify-content:center;padding:20px">
<div style="background:#fff;border-radius:16px;width:100%;max-width:720px;max-height:90vh;overflow-y:auto;padding:30px;position:relative;font-family:Poppins,sans-serif">

    <button onclick="closeBookingModal()" style="position:absolute;top:15px;right:15px;background:none;border:none;font-size:22px;cursor:pointer;color:#888">&times;</button>
    <h2 id="bm-title" style="margin:0 0 20px;font-family:'The Seasons',serif;background:linear-gradient(to right,#5d330f,#dbb595);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent">Add Reservation</h2>

    <form id="bm-form" method="POST" action="/php/reserve_admin.php">
        <input type="hidden" name="reference_number" id="bm-ref-final" value="">
        <input type="hidden" name="reservation_id" id="bm-res-id">

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:30px">

            <!-- LEFT: Guest Info -->
            <div>
                <p style="font-size:13px;font-weight:700;color:#5d330f;text-transform:uppercase;letter-spacing:.06em;margin:0 0 14px;display:flex;align-items:center;gap:6px"><i class="fas fa-user"></i> Guest Information</p>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:12px">
                    <div>
                        <label style="font-size:11px;font-weight:600;color:#888;display:block;margin-bottom:4px">First Name</label>
                        <input type="text" name="first_name" id="bm-first" required
                               style="width:100%;padding:9px 12px;border:1.5px solid #e2ddd8;border-radius:8px;font-family:Poppins,sans-serif;font-size:14px;box-sizing:border-box">
                    </div>
                    <div>
                        <label style="font-size:11px;font-weight:600;color:#888;display:block;margin-bottom:4px">Last Name</label>
                        <input type="text" name="last_name" id="bm-last" required
                               style="width:100%;padding:9px 12px;border:1.5px solid #e2ddd8;border-radius:8px;font-family:Poppins,sans-serif;font-size:14px;box-sizing:border-box">
                    </div>
                </div>

                <div style="margin-bottom:12px">
                    <label style="font-size:11px;font-weight:600;color:#888;display:block;margin-bottom:4px">Phone Number</label>
                    <input type="text" name="phone_number" id="bm-phone" required
                           style="width:100%;padding:9px 12px;border:1.5px solid #e2ddd8;border-radius:8px;font-family:Poppins,sans-serif;font-size:14px;box-sizing:border-box">
                </div>

                <div style="margin-bottom:12px">
                    <label style="font-size:11px;font-weight:600;color:#888;display:block;margin-bottom:4px">Special Request</label>
                    <textarea name="extra_requests" id="bm-requests" rows="3"
                              style="width:100%;padding:9px 12px;border:1.5px solid #e2ddd8;border-radius:8px;font-family:Poppins,sans-serif;font-size:14px;resize:vertical;box-sizing:border-box"></textarea>
                </div>

                <div>
                    <label style="font-size:11px;font-weight:600;color:#888;display:block;margin-bottom:4px">Payment Method</label>
                    <select name="payment_method" id="bm-pay"
                            onchange="bmSelectPayMethod(this.value)"
                            style="width:100%;padding:9px 12px;border:1.5px solid #e2ddd8;border-radius:8px;font-family:Poppins,sans-serif;font-size:14px;box-sizing:border-box">
                        <option value="Cash">Cash – Pay on arrival</option>
                        <option value="GCash">GCash</option>
                        <option value="Card">Card</option>
                    </select>
                </div>

                <!-- GCash Panel -->
                <div id="bm-gcash-panel" style="display:none;margin-top:12px">
                    <div style="background:#f0f6ff;border:1.5px solid #93c5fd;border-radius:12px;padding:16px;display:flex;flex-direction:column;align-items:center;gap:10px">
                        <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#1d4ed8;margin:0">Scan to Pay via GCash</p>
                        <img src="../assets/gcash-qr.jpeg" alt="GCash QR Code"
                             onerror="this.style.display='none';document.getElementById('bm-gcash-qr-fallback').style.display='flex'"
                             style="width:140px;height:140px;object-fit:contain;border-radius:10px;border:2px solid #bfdbfe;display:block">
                        <div id="bm-gcash-qr-fallback" style="display:none;width:140px;height:140px;background:#dbeafe;border-radius:10px;border:2px dashed #93c5fd;align-items:center;justify-content:center;flex-direction:column;gap:6px">
                            <i class="fas fa-qrcode" style="font-size:40px;color:#93c5fd"></i>
                            <span style="font-size:11px;color:#1d4ed8;font-weight:600">QR Code</span>
                        </div>
                        <div style="text-align:center">
                            <p style="margin:0 0 2px;font-size:12px;color:#555">Send to GCash number</p>
                            <p style="font-size:18px;font-weight:800;color:#1d4ed8;letter-spacing:.05em;margin:0">0977 183 7288</p>
                            <p style="font-size:11px;color:#7c746b;margin:4px 0 0">Rawis Resort Hotel</p>
                        </div>
                        <div style="width:100%">
                            <label style="display:block;font-size:11px;font-weight:600;color:#666;margin-bottom:4px">GCash Reference Number</label>
                            <input type="text" id="bm-gcash-ref" placeholder="e.g. 2024031512345678" oninput="document.getElementById('bm-ref-final').value=this.value"
                                   style="width:100%;padding:9px 12px;border:1.5px solid #e2ddd8;border-radius:8px;font-family:Poppins,sans-serif;font-size:13px;box-sizing:border-box">
                        </div>
                    </div>
                </div>

                <!-- Card Panel -->
                <div id="bm-card-panel" style="display:none;margin-top:12px">
                    <div style="background:#fdf6f0;border:1.5px solid #dbb595;border-radius:12px;padding:16px;">
                        <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#8e4a0f;margin:0 0 12px;display:flex;align-items:center;gap:6px">
                            <i class="fas fa-lock"></i> Card Details
                        </p>
                        <div style="margin-bottom:10px">
                            <label style="display:block;font-size:11px;font-weight:600;color:#666;margin-bottom:4px">Card Number</label>
                            <input type="text" id="bm-card-number" placeholder="1234 5678 9012 3456"
                                   maxlength="19" autocomplete="cc-number"
                                   oninput="bmFormatCardNumber(this)"
                                   style="width:100%;padding:9px 12px;border:1.5px solid #e2ddd8;border-radius:8px;letter-spacing:.08em;font-size:14px;font-family:Poppins,sans-serif;box-sizing:border-box">
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                            <div>
                                <label style="display:block;font-size:11px;font-weight:600;color:#666;margin-bottom:4px">Expiry Date</label>
                                <input type="text" id="bm-card-expiry" placeholder="MM / YY"
                                       maxlength="7" autocomplete="cc-exp"
                                       oninput="bmFormatExpiry(this)"
                                       style="width:100%;padding:9px 12px;border:1.5px solid #e2ddd8;border-radius:8px;font-family:Poppins,sans-serif;box-sizing:border-box">
                            </div>
                            <div>
                                <label style="display:block;font-size:11px;font-weight:600;color:#666;margin-bottom:4px">CVC</label>
                                <input type="password" id="bm-card-cvc" placeholder="•••"
                                       maxlength="4" autocomplete="cc-csc"
                                       style="width:100%;padding:9px 12px;border:1.5px solid #e2ddd8;border-radius:8px;letter-spacing:.2em;font-family:Poppins,sans-serif;box-sizing:border-box">
                            </div>
                        </div>
                        <p style="font-size:11px;color:#7c746b;margin:10px 0 0;display:flex;align-items:center;gap:5px">
                            <i class="fas fa-shield-alt" style="color:#dbb595"></i>
                            Card details are used only to process this reservation.
                        </p>
                    </div>
                </div>
            </div>

            <!-- RIGHT: Reservation Summary -->
            <div>
                <p style="font-size:13px;font-weight:700;color:#5d330f;text-transform:uppercase;letter-spacing:.06em;margin:0 0 14px;display:flex;align-items:center;gap:6px"><i class="fas fa-calendar-check"></i> Reservation Summary</p>

                <div style="margin-bottom:12px">
                    <label style="font-size:11px;font-weight:600;color:#888;display:block;margin-bottom:4px">Room</label>
                    <select name="room_id" id="bm-room" required onchange="recalcBookingModal()"
                            style="width:100%;padding:9px 12px;border:1.5px solid #e2ddd8;border-radius:8px;font-family:Poppins,sans-serif;font-size:14px;box-sizing:border-box">
                        <option value="">Select a room…</option>
                    </select>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:12px">
                    <div>
                        <label style="font-size:11px;font-weight:600;color:#888;display:block;margin-bottom:4px">Check-In</label>
                        <input type="date" name="check_in_date" id="bm-checkin" required onchange="recalcBookingModal()"
                               style="width:100%;padding:9px 12px;border:1.5px solid #e2ddd8;border-radius:8px;font-family:Poppins,sans-serif;font-size:14px;box-sizing:border-box">
                    </div>
                    <div>
                        <label style="font-size:11px;font-weight:600;color:#888;display:block;margin-bottom:4px">Check-Out</label>
                        <input type="date" name="check_out_date" id="bm-checkout" required onchange="recalcBookingModal()"
                               style="width:100%;padding:9px 12px;border:1.5px solid #e2ddd8;border-radius:8px;font-family:Poppins,sans-serif;font-size:14px;box-sizing:border-box">
                    </div>
                </div>

                <div style="background:#faf8f5;border:1px solid #ede8e1;border-radius:10px;padding:14px;font-size:14px">
                    <div style="display:flex;justify-content:space-between;padding:5px 0;border-bottom:1px dashed #ede8e1">
                        <span style="color:#888">Room Price</span><span id="bm-room-price" style="font-weight:600">₱0</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding:5px 0;border-bottom:1px dashed #ede8e1">
                        <span style="color:#888">Nights</span><span id="bm-nights" style="font-weight:600">0</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:5px 0;border-bottom:1px dashed #ede8e1">
                        <span style="color:#888">Extra Guest</span>
                        <div style="display:flex;align-items:center;gap:6px">
                            <button type="button" onclick="changeBMQty('bm-extra-guest',-1)" style="width:26px;height:26px;border:1px solid #ddd;border-radius:5px;background:#fff;font-size:16px;cursor:pointer;line-height:1">−</button>
                            <input type="number" id="bm-extra-guest" name="extra_guests" value="0" min="0" readonly
                                   style="width:36px;text-align:center;border:1px solid #ddd;border-radius:5px;padding:3px;font-size:13px">
                            <button type="button" onclick="changeBMQty('bm-extra-guest',1)" style="width:26px;height:26px;border:1px solid #ddd;border-radius:5px;background:#fff;font-size:16px;cursor:pointer;line-height:1">+</button>
                            <span id="bm-eg-cost" style="font-weight:600;min-width:50px;text-align:right">₱0</span>
                        </div>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:5px 0;border-bottom:1px dashed #ede8e1">
                        <span style="color:#888">Extra Bed</span>
                        <div style="display:flex;align-items:center;gap:6px">
                            <button type="button" onclick="changeBMQty('bm-extra-bed',-1)" style="width:26px;height:26px;border:1px solid #ddd;border-radius:5px;background:#fff;font-size:16px;cursor:pointer;line-height:1">−</button>
                            <input type="number" id="bm-extra-bed" name="extra_beds" value="0" min="0" readonly
                                   style="width:36px;text-align:center;border:1px solid #ddd;border-radius:5px;padding:3px;font-size:13px">
                            <button type="button" onclick="changeBMQty('bm-extra-bed',1)" style="width:26px;height:26px;border:1px solid #ddd;border-radius:5px;background:#fff;font-size:16px;cursor:pointer;line-height:1">+</button>
                            <span id="bm-eb-cost" style="font-weight:600;min-width:50px;text-align:right">₱0</span>
                        </div>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding:8px 0 0;font-size:16px;font-weight:700">
                        <span style="color:#5d330f">Total</span>
                        <span id="bm-total" style="background:linear-gradient(to right,#5d330f,#dbb595);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent">₱0</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Buttons -->
        <div style="display:flex;gap:12px;margin-top:24px;justify-content:flex-end">
            <button type="button" id="bm-delete-btn"
                    onclick="if(confirm('Delete this reservation?')) { window.location.href='reservation.php?delete='+document.getElementById('bm-res-id').value; }"
                    style="padding:10px 28px;background:#e74c3c;color:#fff;border:none;border-radius:50px;font-family:Poppins,sans-serif;font-size:14px;font-weight:700;cursor:pointer;display:none">
                DELETE
            </button>
            <button type="submit" id="bm-submit-btn"
                    style="padding:10px 28px;background:linear-gradient(to right,#5d330f,#dbb595);color:#fff;border:none;border-radius:50px;font-family:Poppins,sans-serif;font-size:14px;font-weight:700;cursor:pointer">
                CONFIRM
            </button>
        </div>
    </form>
</div>
</div>

<script>
document.getElementById('bookingModal').style.display = 'none';
document.getElementById('bookingModal').addEventListener('click', function(e){
    if(e.target===this) closeBookingModal();
});
function bmSelectPayMethod(method) {
    document.getElementById('bm-gcash-panel').style.display = method === 'GCash' ? '' : 'none';
    document.getElementById('bm-card-panel').style.display  = method === 'Card'  ? '' : 'none';
    document.getElementById('bm-ref-final').value = '';
    if (method !== 'GCash') { const r = document.getElementById('bm-gcash-ref'); if (r) r.value = ''; }
    if (method !== 'Card') { ['bm-card-number','bm-card-expiry','bm-card-cvc'].forEach(id => { const el = document.getElementById(id); if (el) el.value = ''; }); }
}
function bmFormatCardNumber(input) {
    let v = input.value.replace(/\D/g, '').substring(0, 16);
    input.value = v.replace(/(.{4})/g, '$1 ').trim();
    if (v.length >= 4) document.getElementById('bm-ref-final').value = 'CARD-XXXX-' + v.slice(-4);
}
function bmFormatExpiry(input) {
    let v = input.value.replace(/\D/g, '').substring(0, 4);
    if (v.length >= 3) v = v.substring(0,2) + ' / ' + v.substring(2);
    input.value = v;
}
</script>