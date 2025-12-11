<?php


namespace App\Controllers;


use App\Core\Request;
use App\Core\Response;
use App\Models\Tour;
use App\Models\Departure;
use App\Models\Booking;
use App\Models\Traveler;
use App\Models\Payment;
use App\Models\TourItinerary; // [MỚI] Thêm Model này
use App\Support\Validator;
use App\Core\DB;


class CustomTourBookingController extends BaseController
{
    const CUSTOM_CATEGORY_ID = 99;


    public function create(Request $req): Response
    {
        return $this->render('booking/custom_create', ['errors' => [], 'old' => []]);
    }


    public function store(Request $req): Response
    {
        if (session_status() === PHP_SESSION_NONE) session_start();


        // ... (Giữ nguyên phần gom dữ liệu và validate như cũ) ...
        $tourName      = trim((string)$req->input('custom_tour_name'));
        $startDate     = $req->input('custom_start_date');
        $endDate       = $req->input('custom_end_date');
        $totalAmount   = (float)$req->input('custom_total_amount');
        $depositAmount = (float)$req->input('custom_deposit_amount');
        $contactName   = trim((string)$req->input('contact_name'));
        $contactPhone  = trim((string)$req->input('contact_phone'));
        $travelersData = $req->input('travelers') ?? [];
        $paxCount      = count($travelersData);
        $data = $req->all();


        $rules = [
            'custom_tour_name' => 'required|max:255',
            'custom_start_date'=> 'required|date',
            'custom_end_date'  => 'required|date',
            'custom_total_amount'   => 'required|numeric|min:0',
            'custom_deposit_amount' => 'required|numeric|min:0',
            'contact_name'     => 'required|max:255',
            'contact_phone'    => 'required|max:30',
        ];


        $v = new Validator($data, $rules);
        if ($v->fails()) {
            return $this->render('booking/custom_create', ['errors' => $v->errors(), 'old' => $data]);
        }


        if ($totalAmount > 0 && $depositAmount < ($totalAmount * 0.1)) {
            $_SESSION['flash_error'] = "Tiền cọc chưa đủ 10%.";
            return $this->render('booking/custom_create', ['errors' => [], 'old' => $data]);
        }


        try {
            // ... (Phần tạo Tour, Departure, Booking, Traveler, Payment giữ nguyên) ...
           
            // A. TẠO TOUR MỚI
            $tourCode = 'PV-' . date('ymd') . '-' . rand(100, 999);
            $tourId = (new Tour())->create([
                'code'        => $tourCode,
                'name'        => $tourName,
                'category_id' => self::CUSTOM_CATEGORY_ID,
                'description' => "Tour thiết kế riêng cho khách: $contactName.",
                'state'       => 'PUBLISHED',
                'is_active'   => 1,
                'is_custom'   => 1
            ]);


            // B. TẠO LỊCH KHỞI HÀNH
            $departureId = (new Departure())->create([
                'tour_id'      => $tourId,
                'start_date'   => $startDate,
                'end_date'     => $endDate,
                'capacity'     => $paxCount > 0 ? $paxCount : 1,
                'pickup_point' => 'Theo yêu cầu',
                'status'       => 'OPEN'
            ]);


            // C. TẠO BOOKING
            $bookingCode = 'BK-CUS-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid()), 0, 4));
            $bookingId = (new Booking())->create([
                'departure_id'  => $departureId,
                'code'          => $bookingCode,
                'contact_name'  => $contactName,
                'contact_phone' => $contactPhone,
                'contact_email' => $req->input('contact_email'),
                'note'          => $req->input('note'),
                'pax_count'     => $paxCount,
                'total_amount'  => $totalAmount,
                'state'         => 'DEPOSITED',
                'paid_amount'   => $depositAmount
            ]);


            // D. TẠO PHIẾU THU
            if ($depositAmount > 0) {
                (new Payment())->create([
                    'booking_id' => $bookingId,
                    'amount'     => $depositAmount,
                    'method'     => 'CASH',
                    'receipt_no' => 'PT-' . $bookingCode . '-1',
                    'note'       => 'Thu tiền cọc mở tour theo yêu cầu'
                ]);
            }


            // E. TẠO KHÁCH
            $travelerModel = new Traveler();
            foreach ($travelersData as $t) {
                $travelerModel->create([
                    'booking_id' => $bookingId,
                    'full_name'  => $t['full_name'],
                    'gender'     => $t['gender'] ?? 'OTHER',
                    'dob'        => !empty($t['dob']) ? $t['dob'] : null,
                    'note'       => ''
                ]);
            }


            $_SESSION['flash_success'] = "Đã tạo hồ sơ Booking! Tiếp theo: Vui lòng nhập lịch trình chi tiết.";
           
            // [THAY ĐỔI] Thay vì về trang show, chuyển sang trang nhập lịch trình
            return $this->redirect(route('booking.custom.itinerary', ['id' => $bookingId]));


        } catch (\Throwable $e) {
            error_log("CustomBooking Error: " . $e->getMessage());
            $_SESSION['flash_error'] = "Lỗi hệ thống: " . $e->getMessage();
            return $this->render('booking/custom_create', ['errors' => [], 'old' => $data]);
        }
    }


    /**
     * [GET] Trang nhập lịch trình chi tiết (Bước 2)
     */
    public function editItinerary(Request $req): Response
    {
       
        $bookingId = (int)$req->params['id'];
       
        // Lấy thông tin Booking + Departure + Tour
        $booking = (new Booking())->builder()
            ->select('booking.*, departure.start_date, departure.end_date, departure.tour_id, tour.name as tour_name')
            ->join('departure', 'departure.id', '=', 'booking.departure_id')
            ->join('tour', 'tour.id', '=', 'departure.tour_id')
            ->where('booking.id', $bookingId)
            ->first();


        if (!$booking) {
            $_SESSION['flash_error'] = "Không tìm thấy dữ liệu Booking.";
            return $this->redirect(route('booking.index'));
        }


        // Tính số ngày
        $start = strtotime($booking['start_date']);
        $end   = strtotime($booking['end_date']);
        $datediff = $end - $start;
        $numDays = round($datediff / (60 * 60 * 24)) + 1;
       
        // Lấy lịch trình cũ nếu đã nhập trước đó (để sửa)
        $itineraries = (new TourItinerary())->where('tour_id', $booking['tour_id']);
        $itineraryMap = [];
        foreach($itineraries as $it) {
            $itineraryMap[$it['day_no']] = $it;
        }


        return $this->render('booking/custom_itinerary', [
            'booking' => $booking,
            'numDays' => $numDays,
            'itineraryMap' => $itineraryMap
        ]);
    }


    /**
     * [POST] Lưu lịch trình vào DB
     */
    public function updateItinerary(Request $req): Response
    {
        $bookingId = (int)$req->params['id'];
        $items = $req->input('itinerary') ?? [];
       
        // Lấy tour_id
        $booking = (new Booking())->builder()
             ->select('departure.tour_id')
             ->join('departure', 'departure.id', '=', 'booking.departure_id')
             ->where('booking.id', $bookingId)
             ->first();
             
        if (!$booking) return $this->redirect(route('booking.index'));
       
        $tourId = $booking['tour_id'];
        $itineraryModel = new TourItinerary();


        try {
            foreach ($items as $dayNo => $content) {
                $title = trim($content['title']);
                $desc  = trim($content['content']);
               
                // Nếu trống tiêu đề thì bỏ qua hoặc đặt mặc định
                if (empty($title)) $title = "Ngày $dayNo";


                // Kiểm tra xem ngày này đã có chưa
                $exists = $itineraryModel->builder()
                    ->where('tour_id', $tourId)
                    ->where('day_no', $dayNo)
                    ->first();
                   
                if ($exists) {
                    $itineraryModel->update($exists['id'], [
                        'title' => $title,
                        'content' => $desc
                    ]);
                } else {
                    $itineraryModel->create([
                        'tour_id' => $tourId,
                        'day_no' => $dayNo,
                        'title' => $title,
                        'content' => $desc
                    ]);
                }
            }


            $_SESSION['flash_success'] = "Đã cập nhật lịch trình thành công! Tour đã sẵn sàng.";
            return $this->redirect(route('booking.show', ['id' => $bookingId]));


        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = "Lỗi khi lưu lịch trình: " . $e->getMessage();
            return $this->redirect(route('booking.custom.itinerary', ['id' => $bookingId]));
        }
    }
}

