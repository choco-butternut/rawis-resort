<!-- tuhaya nalain kathlyn -->

<div id="bookingModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);z-index:3000;align-items:center;justify-content:center;padding:20px">
<div style="background:#fff;border-radius:16px;width:100%;max-width:720px;max-height:90vh;overflow-y:auto;padding:30px;position:relative;font-family:Poppins,sans-serif">

    <button onclick="closeBookingModal()" style="position:absolute;top:15px;right:15px;background:none;border:none;font-size:22px;cursor:pointer;color:#888">&times;</button>
    <h2 id="bm-title" style="margin:0 0 20px;font-family:'The Seasons',serif;background:linear-gradient(to right,#5d330f,#dbb595);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent">Add Reservation</h2>

    <form id="bm-form" method="POST" action="/php/reserve_admin.php">
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
                            style="width:100%;padding:9px 12px;border:1.5px solid #e2ddd8;border-radius:8px;font-family:Poppins,sans-serif;font-size:14px;box-sizing:border-box">
                        <option value="Cash">Cash – Pay on arrival</option>
                        <option value="GCash">GCash</option>
                        <option value="Card">Card</option>
                    </select>
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
function openBookingModal(data) {
    const el = document.getElementById('bookingModal');
    el.style.display = 'flex';
    populateRoomSelect();
    if (data) {
        document.getElementById('bm-title').textContent = 'Edit Reservation';
        document.getElementById('bm-res-id').value      = data.id;
        document.getElementById('bm-first').value       = data.firstName;
        document.getElementById('bm-last').value        = data.lastName;
        document.getElementById('bm-phone').value       = data.phone;
        document.getElementById('bm-requests').value    = data.requests || '';
        document.getElementById('bm-pay').value         = data.payMethod || 'Cash';
        document.getElementById('bm-checkin').value     = data.checkIn;
        document.getElementById('bm-checkout').value    = data.checkOut;
        document.getElementById('bm-extra-guest').value = data.extraGuests || 0;
        document.getElementById('bm-extra-bed').value   = data.extraBeds   || 0;
        setTimeout(()=>{ document.getElementById('bm-room').value = data.roomId; recalcBookingModal(); }, 50);
        document.getElementById('bm-delete-btn').style.display = '';
        document.getElementById('bm-submit-btn').textContent   = 'UPDATE';
    } else {
        document.getElementById('bm-title').textContent = 'Add Reservation';
        document.getElementById('bm-res-id').value = '';
        document.getElementById('bm-form').reset();
        document.getElementById('bm-extra-guest').value = 0;
        document.getElementById('bm-extra-bed').value   = 0;
        const today    = new Date().toISOString().split('T')[0];
        const tomorrow = new Date(Date.now()+86400000).toISOString().split('T')[0];
        document.getElementById('bm-checkin').value  = today;
        document.getElementById('bm-checkout').value = tomorrow;
        document.getElementById('bm-delete-btn').style.display = 'none';
        document.getElementById('bm-submit-btn').textContent   = 'CONFIRM';
        recalcBookingModal();
    }
}
function closeBookingModal() {
    document.getElementById('bookingModal').style.display = 'none';
}
</script>