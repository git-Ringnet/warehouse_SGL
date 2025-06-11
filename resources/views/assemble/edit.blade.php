<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Chỉnh sửa phiếu lắp ráp - SGL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
</head>

<body>
    <x-sidebar-component />
    <!-- Main Content -->
    <div class="content-area">
        <header class="bg-white shadow-sm py-4 px-6 flex justify-between items-center sticky top-0 z-40">
            <div class="flex items-center">
                <a href="{{ route('assemblies.index') }}" class="text-gray-600 hover:text-blue-500 mr-4">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-xl font-bold text-gray-800">Chỉnh sửa phiếu lắp ráp</h1>
            </div>
            <div class="flex items-center space-x-2">
                <span class="text-sm text-gray-600">Mã phiếu: <span
                        class="font-medium">{{ $assembly->code }}</span></span>
                <span
                    class="bg-{{ $assembly->status == 'completed' ? 'green' : ($assembly->status == 'in_progress' ? 'yellow' : ($assembly->status == 'cancelled' ? 'red' : 'blue')) }}-100 text-{{ $assembly->status == 'completed' ? 'green' : ($assembly->status == 'in_progress' ? 'yellow' : ($assembly->status == 'cancelled' ? 'red' : 'blue')) }}-800 text-xs px-2 py-1 rounded-full capitalize">
                    {{ $assembly->status == 'in_progress' ? 'Đang thực hiện' : ($assembly->status == 'completed' ? 'Hoàn thành' : ($assembly->status == 'cancelled' ? 'Đã hủy' : 'Chờ xử lý')) }}
                </span>
            </div>
        </header>

        <main class="p-6">
            <form action="{{ route('assemblies.update', $assembly->id) }}" method="POST">
                @csrf
                @method('PUT')

                @if ($errors->any())
                    <div class="mb-4 bg-red-50 p-4 rounded-lg border border-red-200">
                        <div class="text-red-600 font-medium mb-2">Có lỗi xảy ra:</div>
                        <ul class="list-disc pl-5 text-red-500">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Thông tin phiếu lắp ráp -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-tools text-blue-500 mr-2"></i>
                        Thông tin phiếu lắp ráp
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="assembly_code" class="block text-sm font-medium text-gray-700 mb-1">Mã phiếu lắp
                                ráp</label>
                            <div class="flex items-center space-x-2">
                                <input type="text" id="assembly_code" name="assembly_code"
                                    value="{{ $assembly->code }}" readonly
                                    class="w-full border border-gray-300 bg-gray-50 rounded-lg px-3 py-2">
                            </div>
                        </div>
                        <div>
                            <label for="assembly_date"
                                class="block text-sm font-medium text-gray-700 mb-1 required">Ngày lắp ráp <span
                                    class="text-red-500">*</span></label>
                            <input type="date" id="assembly_date" name="assembly_date" value="2023-06-01" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label for="product_id" class="block text-sm font-medium text-gray-700 mb-1 required">Thành
                                phẩm <span class="text-red-500">*</span></label>
                            <div>
                                <div class="relative flex space-x-2">
                                    <select id="product_id"
                                        class="flex-1 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">-- Chọn thành phẩm --</option>
                                        <option value="1" data-name="Máy in Asus AV7-776 (Bảo hành)">
                                            Máy in Asus AV7-776 (Bảo hành)</option>
                                        <option value="2" data-name="Server Acer eAU-928">
                                            Server Acer eAU-928</option>
                                        <option value="3" data-name="Máy in Canon 9N4-811">
                                            Máy in Canon 9N4-811</option>
                                        <option value="4" data-name="Laptop Epson qID-849">
                                            Laptop Epson qID-849</option>
                                        <option value="5" data-name="Máy tính để bàn Canon d8d-337">
                                            Máy tính để bàn Canon d8d-337</option>
                                        <option value="6" data-name="Máy in Apple OGv-254">
                                            Máy in Apple OGv-254</option>
                                        <option value="7" data-name="Server Dell jlX-267">
                                            Server Dell jlX-267</option>
                                        <option value="8" data-name="Phần mềm Samsung pt9-491">
                                            Phần mềm Samsung pt9-491</option>
                                        <option value="9" data-name="Server Canon ZQ7-659 (Bảo hành)">
                                            Server Canon ZQ7-659 (Bảo hành)</option>
                                        <option value="10" data-name="Laptop HP LBU-936">
                                            Laptop HP LBU-936</option>
                                        <option value="11" data-name="Máy in Dell mkB-921">
                                            Máy in Dell mkB-921</option>
                                        <option value="12" data-name="Phần mềm Sony 0sz-382 (Bảo hành)">
                                            Phần mềm Sony 0sz-382 (Bảo hành)</option>
                                        <option value="13" data-name="Phần mềm Microsoft s2H-149 (Bảo hành)">
                                            Phần mềm Microsoft s2H-149 (Bảo hành)</option>
                                        <option value="14" data-name="Laptop Asus YuB-494 (Bảo hành)">
                                            Laptop Asus YuB-494 (Bảo hành)</option>
                                        <option value="15" data-name="Máy in Canon cIv-557">
                                            Máy in Canon cIv-557</option>
                                        <option value="16" data-name="Laptop Apple qpz-911">
                                            Laptop Apple qpz-911</option>
                                        <option value="17" data-name="Laptop HP dbm-565">
                                            Laptop HP dbm-565</option>
                                        <option value="18" data-name="Máy in Asus Cnb-128 (Bảo hành)">
                                            Máy in Asus Cnb-128 (Bảo hành)</option>
                                        <option value="19" data-name="Máy in Asus so6-364">
                                            Máy in Asus so6-364</option>
                                        <option value="20" data-name="Phần mềm Samsung NEo-404">
                                            Phần mềm Samsung NEo-404</option>
                                        <option value="21" data-name="Máy tính để bàn Lenovo 2sc-684">
                                            Máy tính để bàn Lenovo 2sc-684</option>
                                        <option value="22" data-name="Máy in Lenovo ie8-893">
                                            Máy in Lenovo ie8-893</option>
                                        <option value="23" data-name="Phần mềm HP rFS-831">
                                            Phần mềm HP rFS-831</option>
                                        <option value="24" data-name="Server Samsung jXy-195">
                                            Server Samsung jXy-195</option>
                                        <option value="25" data-name="Máy in Sony 2m9-377 (Bảo hành)">
                                            Máy in Sony 2m9-377 (Bảo hành)</option>
                                        <option value="26" data-name="Máy in Microsoft CWn-755">
                                            Máy in Microsoft CWn-755</option>
                                        <option value="27" data-name="Phần mềm Sony sSu-967">
                                            Phần mềm Sony sSu-967</option>
                                        <option value="28" data-name="Máy in Lenovo uJV-330">
                                            Máy in Lenovo uJV-330</option>
                                        <option value="29" data-name="Máy tính để bàn Asus EJd-816">
                                            Máy tính để bàn Asus EJd-816</option>
                                        <option value="30" data-name="Server Sony Kwx-495">
                                            Server Sony Kwx-495</option>
                                        <option value="31" data-name="Laptop Epson QQW-395">
                                            Laptop Epson QQW-395</option>
                                        <option value="32" data-name="Server Dell M5o-306">
                                            Server Dell M5o-306</option>
                                        <option value="33" data-name="Máy in HP Uvx-983">
                                            Máy in HP Uvx-983</option>
                                        <option value="34" data-name="Máy tính để bàn Dell ynq-115">
                                            Máy tính để bàn Dell ynq-115</option>
                                        <option value="35" data-name="Server Sony MIF-163">
                                            Server Sony MIF-163</option>
                                        <option value="36" data-name="Server Sony ba1-934">
                                            Server Sony ba1-934</option>
                                        <option value="37" data-name="Máy tính để bàn Asus KUN-170">
                                            Máy tính để bàn Asus KUN-170</option>
                                        <option value="38" data-name="Laptop Asus 6rm-862 (Bảo hành)">
                                            Laptop Asus 6rm-862 (Bảo hành)</option>
                                        <option value="39" data-name="Laptop Microsoft ZLp-754">
                                            Laptop Microsoft ZLp-754</option>
                                        <option value="40" data-name="Máy in Sony jqa-405 (Bảo hành)">
                                            Máy in Sony jqa-405 (Bảo hành)</option>
                                        <option value="41" data-name="Laptop HP bsi-983">
                                            Laptop HP bsi-983</option>
                                        <option value="42" data-name="Máy in Sony jiG-172">
                                            Máy in Sony jiG-172</option>
                                        <option value="43" data-name="Laptop Apple 2yo-460 (Bảo hành)">
                                            Laptop Apple 2yo-460 (Bảo hành)</option>
                                        <option value="44" data-name="Máy tính để bàn Samsung yLL-545">
                                            Máy tính để bàn Samsung yLL-545</option>
                                        <option value="45" data-name="Server Samsung RuQ-848">
                                            Server Samsung RuQ-848</option>
                                        <option value="46" data-name="Phần mềm Lenovo UWW-241">
                                            Phần mềm Lenovo UWW-241</option>
                                        <option value="47" data-name="Laptop Acer wh0-257">
                                            Laptop Acer wh0-257</option>
                                        <option value="48" data-name="Server Acer 9vd-355 (Bảo hành)">
                                            Server Acer 9vd-355 (Bảo hành)</option>
                                        <option value="49" data-name="Phần mềm Apple BLv-719 (Bảo hành)">
                                            Phần mềm Apple BLv-719 (Bảo hành)</option>
                                        <option value="50" data-name="Máy tính để bàn Dell t97-722">
                                            Máy tính để bàn Dell t97-722</option>
                                        <option value="51" data-name="Phần mềm HP oes-923">
                                            Phần mềm HP oes-923</option>
                                        <option value="52" data-name="Laptop Epson h3E-107">
                                            Laptop Epson h3E-107</option>
                                        <option value="53" data-name="Máy tính để bàn Lenovo FQy-614">
                                            Máy tính để bàn Lenovo FQy-614</option>
                                        <option value="54" data-name="Server Canon 0vH-166">
                                            Server Canon 0vH-166</option>
                                        <option value="55" data-name="Phần mềm Epson wGZ-691">
                                            Phần mềm Epson wGZ-691</option>
                                        <option value="56" data-name="Phần mềm Apple Bib-388 (Bảo hành)">
                                            Phần mềm Apple Bib-388 (Bảo hành)</option>
                                        <option value="57" data-name="Phần mềm Acer rUy-669">
                                            Phần mềm Acer rUy-669</option>
                                        <option value="58" data-name="Server Canon ecR-771">
                                            Server Canon ecR-771</option>
                                        <option value="59" data-name="Phần mềm Apple uWI-154 (Bảo hành)">
                                            Phần mềm Apple uWI-154 (Bảo hành)</option>
                                        <option value="60" data-name="Máy in Canon zzU-285">
                                            Máy in Canon zzU-285</option>
                                        <option value="61" data-name="Server Epson cb9-104 (Bảo hành)">
                                            Server Epson cb9-104 (Bảo hành)</option>
                                        <option value="62" data-name="Server Microsoft ALR-737">
                                            Server Microsoft ALR-737</option>
                                        <option value="63" data-name="Máy tính để bàn Dell aU9-385">
                                            Máy tính để bàn Dell aU9-385</option>
                                        <option value="64" data-name="Máy in Dell Ci9-124 (Bảo hành)">
                                            Máy in Dell Ci9-124 (Bảo hành)</option>
                                        <option value="65" data-name="Laptop Lenovo B00-539">
                                            Laptop Lenovo B00-539</option>
                                        <option value="66" data-name="Phần mềm Epson nAi-312 (Bảo hành)">
                                            Phần mềm Epson nAi-312 (Bảo hành)</option>
                                        <option value="67" data-name="Phần mềm Lenovo wFj-490 (Bảo hành)">
                                            Phần mềm Lenovo wFj-490 (Bảo hành)</option>
                                        <option value="68" data-name="Máy in Dell xGt-673">
                                            Máy in Dell xGt-673</option>
                                        <option value="69" data-name="Laptop Apple MlL-500 (Bảo hành)">
                                            Laptop Apple MlL-500 (Bảo hành)</option>
                                        <option value="70" data-name="Server Apple SdP-519">
                                            Server Apple SdP-519</option>
                                        <option value="71" data-name="Máy in Samsung y25-699">
                                            Máy in Samsung y25-699</option>
                                        <option value="72" data-name="Server Acer AnR-457">
                                            Server Acer AnR-457</option>
                                        <option value="73" data-name="Phần mềm Canon eEX-376">
                                            Phần mềm Canon eEX-376</option>
                                        <option value="74" data-name="Máy in Apple MD0-234 (Bảo hành)">
                                            Máy in Apple MD0-234 (Bảo hành)</option>
                                        <option value="75" data-name="Máy in Epson gpa-132">
                                            Máy in Epson gpa-132</option>
                                        <option value="76" data-name="Máy tính để bàn Epson pqt-960 (Bảo hành)">
                                            Máy tính để bàn Epson pqt-960 (Bảo hành)</option>
                                        <option value="77" data-name="Laptop Epson 38P-678">
                                            Laptop Epson 38P-678</option>
                                        <option value="78" data-name="Phần mềm Samsung upU-202">
                                            Phần mềm Samsung upU-202</option>
                                        <option value="79" data-name="Máy tính để bàn Acer XgU-797">
                                            Máy tính để bàn Acer XgU-797</option>
                                        <option value="80" data-name="Máy in Lenovo AlU-743">
                                            Máy in Lenovo AlU-743</option>
                                        <option value="81" data-name="Laptop Microsoft LKb-425 (Bảo hành)">
                                            Laptop Microsoft LKb-425 (Bảo hành)</option>
                                        <option value="82" data-name="Máy tính để bàn Dell RjV-309">
                                            Máy tính để bàn Dell RjV-309</option>
                                        <option value="83" data-name="Máy in Asus ell-130">
                                            Máy in Asus ell-130</option>
                                        <option value="84" data-name="Máy tính để bàn Lenovo HX5-156 (Bảo hành)">
                                            Máy tính để bàn Lenovo HX5-156 (Bảo hành)</option>
                                        <option value="85" data-name="Máy in Acer 8yb-245">
                                            Máy in Acer 8yb-245</option>
                                        <option value="86" data-name="Server Acer TH0-807">
                                            Server Acer TH0-807</option>
                                        <option value="87" data-name="Phần mềm Apple 9Qp-402">
                                            Phần mềm Apple 9Qp-402</option>
                                        <option value="88" data-name="Máy tính để bàn Canon 9wL-781">
                                            Máy tính để bàn Canon 9wL-781</option>
                                        <option value="89" data-name="Máy tính để bàn Epson YEY-811">
                                            Máy tính để bàn Epson YEY-811</option>
                                        <option value="90" data-name="Server Acer fIp-959">
                                            Server Acer fIp-959</option>
                                        <option value="91" data-name="Máy in Canon 0WK-732 (Bảo hành)">
                                            Máy in Canon 0WK-732 (Bảo hành)</option>
                                        <option value="92" data-name="Máy in Lenovo pZV-931">
                                            Máy in Lenovo pZV-931</option>
                                        <option value="93" data-name="Phần mềm Lenovo c7P-823">
                                            Phần mềm Lenovo c7P-823</option>
                                        <option value="94" data-name="Máy in Dell TVm-506">
                                            Máy in Dell TVm-506</option>
                                        <option value="95" data-name="Laptop Epson tL4-484">
                                            Laptop Epson tL4-484</option>
                                        <option value="96" data-name="Phần mềm Apple syo-787 (Bảo hành)">
                                            Phần mềm Apple syo-787 (Bảo hành)</option>
                                        <option value="97" data-name="Máy tính để bàn Epson BjU-136">
                                            Máy tính để bàn Epson BjU-136</option>
                                        <option value="98" data-name="Laptop Lenovo rhn-221">
                                            Laptop Lenovo rhn-221</option>
                                        <option value="99" data-name="Máy tính để bàn Samsung tRP-708 (Bảo hành)">
                                            Máy tính để bàn Samsung tRP-708 (Bảo hành)</option>
                                        <option value="100" data-name="Phần mềm Samsung VqI-353 (Bảo hành)">
                                            Phần mềm Samsung VqI-353 (Bảo hành)</option>
                                        <option value="101" data-name="Laptop Dell ceh-774 (Bảo hành)">
                                            Laptop Dell ceh-774 (Bảo hành)</option>
                                        <option value="102" data-name="Máy in Lenovo WS8-819 (Bảo hành)">
                                            Máy in Lenovo WS8-819 (Bảo hành)</option>
                                        <option value="103" data-name="Laptop HP 1Hq-210">
                                            Laptop HP 1Hq-210</option>
                                        <option value="104" data-name="Máy in HP 6qh-515">
                                            Máy in HP 6qh-515</option>
                                        <option value="105" data-name="Máy in Canon zcd-185 (Bảo hành)">
                                            Máy in Canon zcd-185 (Bảo hành)</option>
                                        <option value="106" data-name="Máy tính để bàn Microsoft 4WY-550">
                                            Máy tính để bàn Microsoft 4WY-550</option>
                                        <option value="107" data-name="Máy tính để bàn Canon esT-324 (Bảo hành)">
                                            Máy tính để bàn Canon esT-324 (Bảo hành)</option>
                                        <option value="108" data-name="Phần mềm Dell qMa-246 (Bảo hành)">
                                            Phần mềm Dell qMa-246 (Bảo hành)</option>
                                        <option value="109" data-name="Server Apple wxC-294">
                                            Server Apple wxC-294</option>
                                        <option value="110" data-name="Server Dell 1vJ-502">
                                            Server Dell 1vJ-502</option>
                                        <option value="111" data-name="Laptop Sony XBu-838">
                                            Laptop Sony XBu-838</option>
                                        <option value="112" data-name="Phần mềm Apple vCj-583">
                                            Phần mềm Apple vCj-583</option>
                                        <option value="113" data-name="Phần mềm Epson CBS-981 (Bảo hành)">
                                            Phần mềm Epson CBS-981 (Bảo hành)</option>
                                        <option value="114" data-name="Server Asus qXn-111">
                                            Server Asus qXn-111</option>
                                        <option value="115" data-name="Phần mềm Acer mDX-583 (Bảo hành)">
                                            Phần mềm Acer mDX-583 (Bảo hành)</option>
                                        <option value="116" data-name="Laptop Asus Qhl-804">
                                            Laptop Asus Qhl-804</option>
                                        <option value="117" data-name="Máy in Microsoft Fgc-641">
                                            Máy in Microsoft Fgc-641</option>
                                        <option value="118" data-name="Máy tính để bàn Apple wXU-754">
                                            Máy tính để bàn Apple wXU-754</option>
                                        <option value="119" data-name="Server Sony XHf-442 (Bảo hành)">
                                            Server Sony XHf-442 (Bảo hành)</option>
                                        <option value="120" data-name="Laptop Sony Kh2-677">
                                            Laptop Sony Kh2-677</option>
                                        <option value="121" data-name="Phần mềm Canon 8Pc-740 (Bảo hành)">
                                            Phần mềm Canon 8Pc-740 (Bảo hành)</option>
                                        <option value="122" data-name="Phần mềm Lenovo qqw-878">
                                            Phần mềm Lenovo qqw-878</option>
                                        <option value="123" data-name="Phần mềm Acer GZi-366">
                                            Phần mềm Acer GZi-366</option>
                                        <option value="124" data-name="Máy tính để bàn HP 9oe-708 (Bảo hành)">
                                            Máy tính để bàn HP 9oe-708 (Bảo hành)</option>
                                        <option value="125" data-name="Máy in Canon dAx-875">
                                            Máy in Canon dAx-875</option>
                                        <option value="126" data-name="Phần mềm Apple O96-954">
                                            Phần mềm Apple O96-954</option>
                                        <option value="127" data-name="Máy in Lenovo mEb-655 (Bảo hành)">
                                            Máy in Lenovo mEb-655 (Bảo hành)</option>
                                        <option value="128" data-name="Laptop Epson ZnN-155">
                                            Laptop Epson ZnN-155</option>
                                        <option value="129" data-name="Máy in Sony U4A-451">
                                            Máy in Sony U4A-451</option>
                                        <option value="130" data-name="Phần mềm Dell ZCm-215 (Bảo hành)">
                                            Phần mềm Dell ZCm-215 (Bảo hành)</option>
                                        <option value="131" data-name="Server Dell PGI-671">
                                            Server Dell PGI-671</option>
                                        <option value="132" data-name="Server Microsoft hKV-198">
                                            Server Microsoft hKV-198</option>
                                        <option value="133" data-name="Phần mềm Sony cxG-569 (Bảo hành)">
                                            Phần mềm Sony cxG-569 (Bảo hành)</option>
                                        <option value="134" data-name="Máy in Apple H7U-495">
                                            Máy in Apple H7U-495</option>
                                        <option value="135" data-name="Phần mềm Canon IvD-266">
                                            Phần mềm Canon IvD-266</option>
                                        <option value="136" data-name="Máy in Samsung m6n-767">
                                            Máy in Samsung m6n-767</option>
                                        <option value="137" data-name="Máy in Acer 2OK-140 (Bảo hành)">
                                            Máy in Acer 2OK-140 (Bảo hành)</option>
                                        <option value="138" data-name="Phần mềm Sony QpW-475 (Bảo hành)">
                                            Phần mềm Sony QpW-475 (Bảo hành)</option>
                                        <option value="139" data-name="Laptop Microsoft rGQ-818">
                                            Laptop Microsoft rGQ-818</option>
                                        <option value="140" data-name="Máy tính để bàn Lenovo KhG-251">
                                            Máy tính để bàn Lenovo KhG-251</option>
                                        <option value="141" data-name="Laptop Acer kam-745 (Bảo hành)">
                                            Laptop Acer kam-745 (Bảo hành)</option>
                                        <option value="142" data-name="Máy in Microsoft NKI-784 (Bảo hành)">
                                            Máy in Microsoft NKI-784 (Bảo hành)</option>
                                        <option value="143" data-name="Máy in Asus IlA-716 (Bảo hành)">
                                            Máy in Asus IlA-716 (Bảo hành)</option>
                                        <option value="144" data-name="Laptop Dell UuI-225">
                                            Laptop Dell UuI-225</option>
                                        <option value="145" data-name="Laptop Asus jqA-200 (Bảo hành)">
                                            Laptop Asus jqA-200 (Bảo hành)</option>
                                        <option value="146" data-name="Máy in Canon WmX-857">
                                            Máy in Canon WmX-857</option>
                                        <option value="147" data-name="Laptop HP NEa-254 (Bảo hành)">
                                            Laptop HP NEa-254 (Bảo hành)</option>
                                        <option value="148" data-name="Máy tính để bàn Canon LjR-617">
                                            Máy tính để bàn Canon LjR-617</option>
                                        <option value="149" data-name="Server Acer 0WV-413">
                                            Server Acer 0WV-413</option>
                                        <option value="150" data-name="Server Lenovo Tpo-811 (Bảo hành)">
                                            Server Lenovo Tpo-811 (Bảo hành)</option>
                                        <option value="151" data-name="Laptop Apple 7rv-541">
                                            Laptop Apple 7rv-541</option>
                                        <option value="152" data-name="Server Asus CAF-531">
                                            Server Asus CAF-531</option>
                                        <option value="153" data-name="Laptop Canon 3Ph-432">
                                            Laptop Canon 3Ph-432</option>
                                        <option value="154" data-name="Máy in Lenovo jwF-549">
                                            Máy in Lenovo jwF-549</option>
                                        <option value="155" data-name="Server Apple g0D-385 (Bảo hành)">
                                            Server Apple g0D-385 (Bảo hành)</option>
                                        <option value="156" data-name="Máy in Acer z3s-758 (Bảo hành)">
                                            Máy in Acer z3s-758 (Bảo hành)</option>
                                        <option value="157" data-name="Server Dell EM1-320 (Bảo hành)">
                                            Server Dell EM1-320 (Bảo hành)</option>
                                        <option value="158" data-name="Máy tính để bàn Asus IMu-135 (Bảo hành)">
                                            Máy tính để bàn Asus IMu-135 (Bảo hành)</option>
                                        <option value="159" data-name="Phần mềm Canon 7Dm-681 (Bảo hành)">
                                            Phần mềm Canon 7Dm-681 (Bảo hành)</option>
                                        <option value="160" data-name="Laptop Samsung wrL-316">
                                            Laptop Samsung wrL-316</option>
                                        <option value="161" data-name="Server Asus PxV-661 (Bảo hành)">
                                            Server Asus PxV-661 (Bảo hành)</option>
                                        <option value="162" data-name="Phần mềm Acer 5RY-502">
                                            Phần mềm Acer 5RY-502</option>
                                        <option value="163" data-name="Server Dell tWZ-739 (Bảo hành)">
                                            Server Dell tWZ-739 (Bảo hành)</option>
                                        <option value="164" data-name="Máy tính để bàn Asus Zqh-504">
                                            Máy tính để bàn Asus Zqh-504</option>
                                        <option value="165" data-name="Máy tính để bàn Samsung Rsi-690 (Bảo hành)">
                                            Máy tính để bàn Samsung Rsi-690 (Bảo hành)</option>
                                        <option value="166" data-name="Máy in Epson POR-758 (Bảo hành)">
                                            Máy in Epson POR-758 (Bảo hành)</option>
                                        <option value="167" data-name="Laptop Epson 37v-984 (Bảo hành)">
                                            Laptop Epson 37v-984 (Bảo hành)</option>
                                        <option value="168" data-name="Phần mềm Apple ad9-436">
                                            Phần mềm Apple ad9-436</option>
                                        <option value="169" data-name="Phần mềm Asus C55-942">
                                            Phần mềm Asus C55-942</option>
                                        <option value="170" data-name="Phần mềm Samsung yWN-224">
                                            Phần mềm Samsung yWN-224</option>
                                        <option value="171" data-name="Máy tính để bàn Asus rnK-348 (Bảo hành)">
                                            Máy tính để bàn Asus rnK-348 (Bảo hành)</option>
                                        <option value="172" data-name="Server Apple Tq8-853">
                                            Server Apple Tq8-853</option>
                                        <option value="173" data-name="Máy in HP cx7-300 (Bảo hành)">
                                            Máy in HP cx7-300 (Bảo hành)</option>
                                        <option value="174" data-name="Server Dell zdk-597 (Bảo hành)">
                                            Server Dell zdk-597 (Bảo hành)</option>
                                        <option value="175" data-name="Server HP TyY-809">
                                            Server HP TyY-809</option>
                                        <option value="176" data-name="Server Microsoft J8I-395">
                                            Server Microsoft J8I-395</option>
                                        <option value="177" data-name="Máy tính để bàn Lenovo vBZ-152">
                                            Máy tính để bàn Lenovo vBZ-152</option>
                                        <option value="178" data-name="Phần mềm Lenovo khz-878 (Bảo hành)">
                                            Phần mềm Lenovo khz-878 (Bảo hành)</option>
                                        <option value="179" data-name="Server HP 05p-482">
                                            Server HP 05p-482</option>
                                        <option value="180" data-name="Máy tính để bàn Dell 91k-980">
                                            Máy tính để bàn Dell 91k-980</option>
                                        <option value="181" data-name="Phần mềm Epson DEm-949">
                                            Phần mềm Epson DEm-949</option>
                                        <option value="182" data-name="Máy in Acer bxY-399 (Bảo hành)">
                                            Máy in Acer bxY-399 (Bảo hành)</option>
                                        <option value="183" data-name="Server Samsung 1zn-581 (Bảo hành)">
                                            Server Samsung 1zn-581 (Bảo hành)</option>
                                        <option value="184" data-name="Server Sony l4o-923 (Bảo hành)">
                                            Server Sony l4o-923 (Bảo hành)</option>
                                        <option value="185" data-name="Máy tính để bàn HP NQt-216">
                                            Máy tính để bàn HP NQt-216</option>
                                        <option value="186" data-name="Máy tính để bàn HP GAp-625">
                                            Máy tính để bàn HP GAp-625</option>
                                        <option value="187" data-name="Phần mềm Sony CVA-115">
                                            Phần mềm Sony CVA-115</option>
                                        <option value="188" data-name="Laptop Sony VCq-286">
                                            Laptop Sony VCq-286</option>
                                        <option value="189" data-name="Máy in Lenovo EVp-888">
                                            Máy in Lenovo EVp-888</option>
                                        <option value="190" data-name="Phần mềm Samsung N0V-373">
                                            Phần mềm Samsung N0V-373</option>
                                        <option value="191" data-name="Máy tính để bàn Samsung FA7-292">
                                            Máy tính để bàn Samsung FA7-292</option>
                                        <option value="192" data-name="Máy tính để bàn HP gzF-896">
                                            Máy tính để bàn HP gzF-896</option>
                                        <option value="193" data-name="Laptop Epson YLm-761">
                                            Laptop Epson YLm-761</option>
                                        <option value="194" data-name="Server Acer P6Y-771">
                                            Server Acer P6Y-771</option>
                                        <option value="195" data-name="Server Apple g8O-104">
                                            Server Apple g8O-104</option>
                                        <option value="196" data-name="Laptop Dell 0G8-902">
                                            Laptop Dell 0G8-902</option>
                                        <option value="197" data-name="Phần mềm Microsoft 7mw-735">
                                            Phần mềm Microsoft 7mw-735</option>
                                        <option value="198" data-name="Laptop Lenovo FSK-405 (Bảo hành)">
                                            Laptop Lenovo FSK-405 (Bảo hành)</option>
                                        <option value="199" data-name="Máy in Apple KY3-408 (Bảo hành)">
                                            Máy in Apple KY3-408 (Bảo hành)</option>
                                        <option value="200" data-name="Máy tính để bàn Apple D2t-933">
                                            Máy tính để bàn Apple D2t-933</option>
                                    </select>
                                    <div class="w-24">
                                        <input type="number" id="product_add_quantity" min="1"
                                            value="1"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            placeholder="Số lượng">
                                    </div>
                                    <button type="button" id="add_product_btn"
                                        class="px-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                                        Thêm
                                    </button>
                                </div>
                                <div class="text-xs text-gray-500 mt-1">Chọn thành phẩm, nhập số lượng và nhấn thêm. Có
                                    thể
                                    thêm nhiều thành phẩm.</div>
                            </div>
                        </div>
                        <div>
                            <label for="assigned_to"
                                class="block text-sm font-medium text-gray-700 mb-1 required">Người
                                phụ trách <span class="text-red-500">*</span></label>
                            <select id="assigned_to" name="assigned_to" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">-- Chọn người phụ trách --</option>
                                <option value="Nguyễn Văn A"
                                    {{ $assembly->assigned_to == 'Nguyễn Văn A' ? 'selected' : '' }}>Nguyễn Văn A
                                </option>
                                <option value="Trần Thị B"
                                    {{ $assembly->assigned_to == 'Trần Thị B' ? 'selected' : '' }}>Trần Thị B</option>
                                <option value="Lê Văn C" {{ $assembly->assigned_to == 'Lê Văn C' ? 'selected' : '' }}>
                                    Lê
                                    Văn C</option>
                                <option value="Phạm Thị D"
                                    {{ $assembly->assigned_to == 'Phạm Thị D' ? 'selected' : '' }}>Phạm Thị D</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label for="tester_id" class="block text-sm font-medium text-gray-700 mb-1 required">Người
                                tiếp nhận kiểm thử <span class="text-red-500">*</span></label>
                            <select id="tester_id" name="tester_id" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">-- Chọn người tiếp nhận kiểm thử --</option>
                                <option value="Nguyễn Văn A"
                                    {{ $assembly->tester_id == 'Nguyễn Văn A' ? 'selected' : '' }}>Nguyễn Văn A
                                </option>
                                <option value="Trần Thị B"
                                    {{ $assembly->tester_id == 'Trần Thị B' ? 'selected' : '' }}>Trần Thị B</option>
                                <option value="Lê Văn C" {{ $assembly->tester_id == 'Lê Văn C' ? 'selected' : '' }}>Lê
                                    Văn C</option>
                                <option value="Phạm Thị D"
                                    {{ $assembly->tester_id == 'Phạm Thị D' ? 'selected' : '' }}>Phạm Thị D</option>
                            </select>
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1 required">Trạng
                                thái <span class="text-red-500">*</span></label>
                            <select id="status" name="status" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="pending" {{ $assembly->status == 'pending' ? 'selected' : '' }}>Chờ xử
                                    lý</option>
                                <option value="in_progress"
                                    {{ $assembly->status == 'in_progress' ? 'selected' : '' }}>Đang thực hiện</option>
                                <option value="completed" {{ $assembly->status == 'completed' ? 'selected' : '' }}>
                                    Hoàn thành</option>
                                <option value="cancelled" {{ $assembly->status == 'cancelled' ? 'selected' : '' }}>Đã
                                    hủy</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                        <div>
                            <label for="warehouse_id"
                                class="block text-sm font-medium text-gray-700 mb-1 required">Kho xuất
                                <span class="text-red-500">*</span></label>
                            <select id="warehouse_id" name="warehouse_id" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">-- Chọn kho xuất linh kiện --</option>
                                @foreach ($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}"
                                        {{ $assembly->warehouse_id == $warehouse->id ? 'selected' : '' }}>
                                        {{ $warehouse->name }} ({{ $warehouse->code }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div id="target_warehouse_container">
                            <label for="target_warehouse_id"
                                class="block text-sm font-medium text-gray-700 mb-1 required">Kho nhập
                                <span class="text-red-500">*</span></label>
                            <select id="target_warehouse_id" name="target_warehouse_id" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">-- Chọn kho nhập thành phẩm --</option>
                                @foreach ($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}"
                                        {{ $assembly->target_warehouse_id == $warehouse->id ? 'selected' : '' }}>
                                        {{ $warehouse->name }} ({{ $warehouse->code }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div id="project_selection" class="{{ $assembly->purpose != 'project' ? 'hidden' : '' }}">
                            <div>
                                <label for="project_id" class="block text-sm font-medium text-gray-700 mb-1 required">Dự
                                    án <span class="text-red-500">*</span></label>
                                <select id="project_id" name="project_id"
                                    {{ $assembly->purpose == 'project' ? 'required' : '' }}
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">-- Chọn dự án --</option>
                                    @foreach ($projects ?? [] as $project)
                                        <option value="{{ $project->id }}"
                                            {{ $assembly->project_id == $project->id ? 'selected' : '' }}>
                                            {{ $project->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="purpose" class="block text-sm font-medium text-gray-700 mb-1 required">Mục
                                đích <span class="text-red-500">*</span></label>
                            <select id="purpose" name="purpose" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="storage" {{ $assembly->purpose == 'storage' ? 'selected' : '' }}>Lưu
                                    kho</option>
                                <option value="project" {{ $assembly->purpose == 'project' ? 'selected' : '' }}>Xuất
                                    đi dự án</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4">
                        <div>
                            <label for="assembly_note" class="block text-sm font-medium text-gray-700 mb-1">Ghi
                                chú</label>
                            <textarea id="assembly_note" name="assembly_note" rows="2"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ $assembly->notes }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Danh sách linh kiện -->
                <!-- Thành phẩm đã thêm -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-box-open text-blue-500 mr-2"></i>
                        Thành phẩm đã thêm
                    </h2>

                    <!-- Bảng thành phẩm -->
                    <div class="overflow-x-auto mb-4">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Mã
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Tên thành phẩm
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Số lượng
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Serial
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Thao tác
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="product_list" class="bg-white divide-y divide-gray-200">
                                <!-- Thành phẩm hiện tại -->
                                <tr class="product-row bg-white hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        1
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ $assembly->product->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ $assembly->quantity }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        <input type="text" name="products[0][serials][]" placeholder="Serial 1"
                                            class="w-full border border-gray-300 rounded-lg px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button type="button" class="text-red-500 hover:text-red-700 delete-product" data-index="0"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Danh sách linh kiện -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-microchip text-blue-500 mr-2"></i>
                        Danh sách linh kiện sử dụng
                    </h2>

                    <div class="mt-4 flex items-center space-x-2 mb-4">
                        <div class="flex-grow">
                            <input type="text" id="component_search"
                                placeholder="Nhập hoặc click để xem danh sách linh kiện..."
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <!-- Search Results Popup -->
                            <div id="search_results"
                                class="absolute bg-white mt-1 border border-gray-300 rounded-lg shadow-lg z-10 hidden w-full max-w-2xl max-h-60 overflow-y-auto">
                                <!-- Search results will be populated here -->
                            </div>
                        </div>
                        <div class="w-24">
                            <input type="number" id="component_add_quantity" min="1" value="1"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <button id="add_component_btn"
                                class="px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none flex items-center">
                                <i class="fas fa-plus mr-1"></i> Thêm
                            </button>
                        </div>
                    </div>

                    <!-- Component blocks container -->
                    <div id="component_blocks_container" class="mb-4">
                        <div id="component_block_product_1" class="mb-6 border border-gray-200 rounded-lg">
                            <div class="bg-blue-50 px-4 py-2 rounded-t-lg flex items-center justify-between">
                                <div class="font-medium text-blue-800 flex items-center">
                                    <i class="fas fa-box-open mr-2"></i>
                                    <span>Linh kiện cho: {{ $assembly->product->name }}</span>
                                    <span class="ml-2 text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">
                                        {{ $assembly->quantity }} thành phẩm
                                    </span>
                                </div>
                                <button type="button" class="toggle-components text-blue-700 hover:text-blue-900">
                                    <i class="fas fa-chevron-up"></i>
                                </button>
                            </div>
                            <div class="component-list-container p-4">
                                <!-- Bảng linh kiện cho thành phẩm -->
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Mã
                                                </th>
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Loại linh kiện
                                                </th>
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Tên linh kiện
                                                </th>
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Số lượng
                                                </th>
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Serial
                                                </th>
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Ghi chú
                                                </th>
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Thao tác
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody id="component_list" class="bg-white divide-y divide-gray-200">
                                            <!-- thành phẩm hiện tại -->
                                            @foreach ($assembly->materials as $index => $material)
                                                <tr class="component-row bg-white hover:bg-gray-50">
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        <input type="hidden"
                                                            name="components[{{ $index }}][id]"
                                                            value="{{ $material->material_id }}">
                                                        {{ $material->material->code }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                        {{ $material->material->category }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                        {{ $material->material->name }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                        <input type="number" min="1"
                                                            name="components[{{ $index }}][quantity]"
                                                            value="{{ $material->quantity }}"
                                                            class="w-20 border border-gray-300 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                        <input type="text"
                                                            name="components[{{ $index }}][serial]"
                                                            value="{{ $material->serial ?? '' }}"
                                                            class="w-full border border-gray-300 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                            placeholder="Nhập serial">
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                        <input type="text"
                                                            name="components[{{ $index }}][note]"
                                                            value="{{ $material->note ?? '' }}"
                                                            class="w-full border border-gray-300 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                            placeholder="Ghi chú">
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                        <button type="button"
                                                            class="text-red-500 hover:text-red-700 delete-component"
                                                            data-index="{{ $index }}">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                            <!-- Hàng "không có linh kiện" -->
                                            <tr id="no_components_row"
                                                style="{{ count($assembly->materials) > 0 ? 'display: none;' : '' }}">
                                                <td colspan="7"
                                                    class="px-6 py-4 text-sm text-gray-500 text-center">
                                                    Chưa có linh kiện nào được thêm vào thành phẩm này
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    <a href="{{ route('assemblies.index') }}"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-5 py-2 rounded-lg transition-colors flex items-center">
                        <i class="fas fa-times mr-2"></i> Hủy
                    </a>
                    <button type="submit" id="submit-btn"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-5 py-2 rounded-lg transition-colors flex items-center">
                        <i class="fas fa-save mr-2"></i> Cập nhật phiếu
                    </button>
                </div>
            </form>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Component blocks toggle
            document.querySelectorAll('.toggle-components').forEach(button => {
                button.addEventListener('click', function() {
                    const block = this.closest('.mb-6');
                    const container = block.querySelector('.component-list-container');
                    const icon = this.querySelector('i');

                    if (container.style.display === 'none') {
                        container.style.display = 'block';
                        icon.className = 'fas fa-chevron-up';
                    } else {
                        container.style.display = 'none';
                        icon.className = 'fas fa-chevron-down';
                    }
                });
            });

            // Xử lý tìm kiếm linh kiện khi gõ
            const componentSearchInput = document.getElementById('component_search');
            const addComponentBtn = document.getElementById('add_component_btn');
            const componentList = document.getElementById('component_list');
            const noComponentsRow = document.getElementById('no_components_row');
            const searchResults = document.getElementById('search_results');
            const productSelect = document.getElementById('product_id');
            const warehouseSelect = document.getElementById('warehouse_id');
            const componentAddQuantity = document.getElementById('component_add_quantity');

            // Component blocks toggle
            document.querySelectorAll('.toggle-components').forEach(button => {
                button.addEventListener('click', function() {
                    const block = this.closest('.mb-6');
                    const container = block.querySelector('.component-list-container');
                    const icon = this.querySelector('i');

                    if (container.style.display === 'none') {
                        container.style.display = 'block';
                        icon.className = 'fas fa-chevron-up';
                    } else {
                        container.style.display = 'none';
                        icon.className = 'fas fa-chevron-down';
                    }
                });
            });

            let searchTimeout = null;
            let selectedMaterial = null;
            let warehouseMaterials = [];

            // Ensure component quantity is at least 1
            componentAddQuantity.addEventListener('change', function() {
                if (parseInt(this.value) < 1) {
                    this.value = 1;
                }
            });

            // Khởi tạo mảng linh kiện đã chọn từ dữ liệu hiện có
            let selectedComponents = [];

            // Tải linh kiện đã có vào mảng
            document.querySelectorAll('.component-row').forEach((row, index) => {
                const idInput = row.querySelector('input[name^="components"][name$="[id]"]');
                const quantityInput = row.querySelector('input[name^="components"][name$="[quantity]"]');
                const serialInput = row.querySelector('input[name^="components"][name$="[serial]"]');
                const noteInput = row.querySelector('input[name^="components"][name$="[note]"]');

                if (idInput) {
                    const code = row.cells[0].textContent.trim();
                    const type = row.cells[1].textContent.trim();
                    const name = row.cells[2].textContent.trim();
                    const quantity = quantityInput ? parseInt(quantityInput.value) : 1;
                    const serial = serialInput ? serialInput.value : '';

                    // Parse multiple serials if comma-separated
                    let serials = [];
                    if (serial && serial.includes(',')) {
                        serials = serial.split(',').map(s => s.trim());
                    }

                    selectedComponents.push({
                        id: idInput.value,
                        code: code,
                        type: type,
                        name: name,
                        quantity: quantity,
                        originalQuantity: quantity, // Store the original quantity for validation
                        serial: serial,
                        serials: serials,
                        note: noteInput ? noteInput.value : '',
                        stock_quantity: 0, // Will be updated when fetching warehouse materials
                        isExisting: true // Flag to mark components already in the assembly
                    });
                }
            });

            // Note: Edit mode doesn't have product quantity input since we're editing existing assembly

            // Kiểm tra kho xuất và kho nhập không được trùng nhau
            function validateWarehouses() {
                const sourceWarehouse = warehouseSelect.value;
                const targetWarehouse = document.getElementById('target_warehouse_id').value;

                if (sourceWarehouse && targetWarehouse && sourceWarehouse === targetWarehouse) {
                    // Hiển thị cảnh báo
                    showWarehouseWarning();
                    return false;
                } else {
                    // Ẩn cảnh báo
                    hideWarehouseWarning();
                    return true;
                }
            }

            function showWarehouseWarning() {
                // Tìm container của kho nhập
                const targetContainer = document.getElementById('target_warehouse_id').parentElement;

                // Xóa cảnh báo cũ nếu có
                const existingWarning = targetContainer.querySelector('.warehouse-warning');
                if (existingWarning) {
                    existingWarning.remove();
                }

                // Tạo cảnh báo mới
                const warningDiv = document.createElement('div');
                warningDiv.className = 'warehouse-warning text-red-600 text-sm mt-1 font-medium';
                warningDiv.textContent = 'Kho nhập thành phẩm phải khác với kho xuất linh kiện!';
                targetContainer.appendChild(warningDiv);

                // Đổi màu border của select
                document.getElementById('target_warehouse_id').classList.add('border-red-500');
                warehouseSelect.classList.add('border-red-500');
            }

            function hideWarehouseWarning() {
                // Xóa cảnh báo
                const existingWarning = document.querySelector('.warehouse-warning');
                if (existingWarning) {
                    existingWarning.remove();
                }

                // Bỏ màu border đỏ
                document.getElementById('target_warehouse_id').classList.remove('border-red-500');
                warehouseSelect.classList.remove('border-red-500');
            }

            // Thêm event listener cho cả hai dropdown kho
            warehouseSelect.addEventListener('change', function() {
                fetchWarehouseMaterials(this.value);
                validateWarehouses();
            });

            document.getElementById('target_warehouse_id').addEventListener('change', function() {
                validateWarehouses();
            });

            // Lấy danh sách linh kiện khi click vào ô tìm kiếm
            componentSearchInput.addEventListener('click', function() {
                const warehouseId = warehouseSelect.value;
                if (!warehouseId) {
                    alert('Vui lòng chọn kho trước khi tìm kiếm linh kiện!');
                    return;
                }

                showAllMaterials();
            });

            // Hàm lấy danh sách linh kiện theo kho
            function fetchWarehouseMaterials(warehouseId) {
                if (!warehouseId) return;

                // Hiển thị đang tải
                warehouseMaterials = [];

                // Gọi API để lấy linh kiện theo kho
                fetch(`/api/warehouses/${warehouseId}/materials`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            console.error('Error fetching warehouse materials:', data.message);
                            return;
                        }

                        // Lưu danh sách vật tư của kho
                        warehouseMaterials = Array.isArray(data) ? data : (data.materials || []);

                        // Update stock quantities for existing components
                        selectedComponents.forEach(component => {
                            const warehouseMaterial = warehouseMaterials.find(m => m.id == component
                                .id);
                            if (warehouseMaterial) {
                                component.stock_quantity = warehouseMaterial.stock_quantity;
                            }
                        });

                        // Update the component list to show stock warnings
                        updateComponentList();
                    })
                    .catch(error => {
                        console.error('Error loading warehouse materials:', error);
                    });
            }

            // Hiển thị tất cả linh kiện của kho
            function showAllMaterials() {
                if (warehouseMaterials.length === 0) {
                    // Nếu chưa có dữ liệu, lấy từ API
                    const warehouseId = warehouseSelect.value;
                    if (!warehouseId) return;

                    // Hiển thị đang tải
                    searchResults.innerHTML =
                        '<div class="p-2 text-gray-500">Đang tải danh sách linh kiện...</div>';
                    searchResults.classList.remove('hidden');

                    // Gọi API để lấy linh kiện theo kho
                    fetch(`/api/warehouses/${warehouseId}/materials`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.error) {
                                searchResults.innerHTML = `<div class="p-2 text-red-500">Lỗi: ${data.message}
                                </div>`;
                                console.error('Error fetching warehouse materials:', data.message);
                                return;
                            }

                            // Lưu và hiển thị danh sách vật tư của kho
                            warehouseMaterials = Array.isArray(data) ? data : (data.materials || []);
                            displaySearchResults(warehouseMaterials);
                        })
                        .catch(error => {
                            console.error('Error loading warehouse materials:', error);
                            searchResults.innerHTML =
                                '<div class="p-2 text-red-500">Có lỗi xảy ra khi tải dữ liệu!</div>';
                        });
                } else {
                    // Hiển thị danh sách đã có
                    displaySearchResults(warehouseMaterials);
                }
            }

            // Hiển thị kết quả tìm kiếm
            function displaySearchResults(materials) {
                if (materials.length === 0) {
                    searchResults.innerHTML =
                        '<div class="p-2 text-gray-500">Không có linh kiện nào trong kho này</div>';
                    return;
                }

                searchResults.innerHTML = '';
                materials.forEach(material => {
                    const resultItem = document.createElement('div');
                    resultItem.className = 'p-2 hover:bg-gray-100 cursor-pointer';
                    resultItem.innerHTML = `
                        <div class="font-medium">${material.code}: ${material.name}</div>
                        <div class="text-xs text-gray-500">
                            ${material.category || ''} 
                            ${material.serial ? '| ' + material.serial : ''} 
                            | Tồn kho: ${material.stock_quantity || 0}
                        </div>
                    `;

                    // Handle click on search result
                    resultItem.addEventListener('click', function() {
                        selectedMaterial = material;
                        componentSearchInput.value = material.code + ' - ' + material.name;
                        searchResults.classList.add('hidden');
                    });

                    searchResults.appendChild(resultItem);
                });

                searchResults.classList.remove('hidden');
            }

            componentSearchInput.addEventListener('input', function() {
                const searchTerm = componentSearchInput.value.trim().toLowerCase();

                // Clear any existing timeout
                if (searchTimeout) {
                    clearTimeout(searchTimeout);
                }

                // Set a timeout to avoid too many searches while typing
                searchTimeout = setTimeout(() => {
                    const warehouseId = warehouseSelect.value;
                    if (!warehouseId) {
                        alert('Vui lòng chọn kho trước khi tìm kiếm linh kiện!');
                        return;
                    }

                    if (searchTerm.length === 0) {
                        // Nếu ô tìm kiếm trống, hiển thị tất cả linh kiện
                        showAllMaterials();
                        return;
                    }

                    // Nếu đã có danh sách linh kiện của kho, lọc trực tiếp
                    if (warehouseMaterials.length > 0) {
                        const filteredMaterials = warehouseMaterials.filter(material =>
                            material.code?.toLowerCase().includes(searchTerm) ||
                            material.name?.toLowerCase().includes(searchTerm) ||
                            material.category?.toLowerCase().includes(searchTerm) ||
                            material.serial?.toLowerCase().includes(searchTerm)
                        );

                        displaySearchResults(filteredMaterials);
                        return;
                    }

                    // Nếu chưa có danh sách linh kiện, tìm kiếm qua API
                    searchResults.innerHTML =
                        '<div class="p-2 text-gray-500">Đang tìm kiếm...</div>';
                    searchResults.classList.remove('hidden');

                    // Gọi API để tìm kiếm linh kiện
                    fetch(`/api/warehouses/${warehouseId}/materials?term=${
                        encodeURIComponent(searchTerm)
                    }`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.error) {
                                searchResults.innerHTML = `<div class="p-2 text-red-500">Lỗi: ${data.message}
                                </div>`;
                                console.error('Error searching materials:', data.message);
                                return;
                            }

                            const materials = Array.isArray(data) ? data : (data.materials ||
                            []);
                            displaySearchResults(materials);
                        })
                        .catch(error => {
                            console.error('Error searching materials:', error);
                            searchResults.innerHTML =
                                '<div class="p-2 text-red-500">Có lỗi xảy ra khi tìm kiếm!</div>';
                        });
                }, 300);
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(event) {
                if (!componentSearchInput.contains(event.target) && !searchResults.contains(event.target)) {
                    searchResults.classList.add('hidden');
                }
            });

            addComponentBtn.addEventListener('click', function() {
                addSelectedComponent();
            });

            // Check if stock is sufficient for a component based on product quantity
            function checkStockSufficiency(component) {
                // Số lượng thành phẩm
                const productQty = {{ $assembly->quantity }};

                // Số lượng linh kiện cho mỗi thành phẩm
                const componentQtyPerProduct = parseInt(component.quantity);

                // Tổng số linh kiện cần = số lượng thành phẩm * số lượng linh kiện cho mỗi thành phẩm
                const totalRequiredQty = productQty * componentQtyPerProduct;

                // For existing components, we only need to check additional quantity beyond original
                let effectiveRequiredQty = totalRequiredQty;
                let additionalQtyNeeded = 0;

                if (component.isExisting && component.originalQuantity) {
                    const originalTotalQty = component.originalQuantity * ({{ $assembly->quantity }});
                    // If we're using less than or equal to original amount, no stock check needed
                    if (totalRequiredQty <= originalTotalQty) {
                        component.isStockSufficient = true;
                        component.stockWarning = '';
                        return true;
                    }
                    // Otherwise, only check the additional quantity needed
                    additionalQtyNeeded = totalRequiredQty - originalTotalQty;
                    // We only need to check if stock can cover this additional amount
                    effectiveRequiredQty = additionalQtyNeeded;
                }

                // Tồn kho hiện có
                const stockQty = parseInt(component.stock_quantity);

                // Check if stock is sufficient for the effective required quantity
                component.isStockSufficient = effectiveRequiredQty <= stockQty;

                if (!component.isStockSufficient) {
                    // Calculate the actual shortage (how many more we need beyond what's in stock)
                    let actualShortage;

                    if (component.isExisting) {
                        // For existing components
                        actualShortage = additionalQtyNeeded - stockQty;
                        component.stockWarning = `Không đủ tồn kho (còn ${stockQty}, cần thêm ${
                            actualShortage
                        })`;
                    } else {
                        // For new components
                        actualShortage = totalRequiredQty - stockQty;
                        component.stockWarning = `Không đủ tồn kho (còn ${stockQty}, cần thêm ${
                            actualShortage
                        })`;
                    }
                } else {
                    component.stockWarning = '';
                }

                return component.isStockSufficient;
            }

            // Update component quantities based on product quantity
            function updateComponentQuantities() {
                // In edit mode, we use the existing assembly quantity
                const productQty = {{ $assembly->quantity }};

                selectedComponents.forEach(component => {
                    // Only update if component doesn't have manually adjusted quantity
                    // AND is not an existing component from the assembly
                    if (!component.manuallyAdjusted && !component.isExisting) {
                        component.quantity = productQty;
                    }

                    // Always check stock sufficiency when product quantity changes
                    checkStockSufficiency(component);
                });

                updateComponentList();
            }

            // Generate serial input fields based on quantity
            function generateSerialInputs(component, index, container) {
                const quantity = parseInt(component.quantity);
                const serialsContainer = document.createElement('div');
                serialsContainer.className = 'serial-inputs mt-2';

                // Clear existing serials container
                const existingContainer = container.querySelector('.serial-inputs');
                if (existingContainer) {
                    existingContainer.remove();
                }

                // Remove any existing serial count label
                const existingLabel = container.querySelector('.serial-count-label');
                if (existingLabel) {
                    existingLabel.remove();
                }

                if (quantity > 1) {
                    // For quantities > 1, show multiple serial inputs
                    // If we have comma-separated serials, split them
                    let serials = component.serials || [];
                    if (!serials.length && component.serial && component.serial.includes(',')) {
                        serials = component.serial.split(',').map(s => s.trim());
                        component.serials = serials;
                    }

                    for (let i = 0; i < quantity; i++) {
                        const serialDiv = document.createElement('div');
                        serialDiv.className = 'mb-1';
                        const serialInput = document.createElement('input');
                        serialInput.type = 'text';
                        serialInput.name = `components[${index}][serials][]`;
                        serialInput.value = serials[i] || '';
                        serialInput.placeholder = `Serial ${i+1}`;
                        serialInput.className =
                            'w-full border border-gray-300 rounded-lg px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500';

                        // Save serial when typing
                        serialInput.addEventListener('input', function() {
                            if (!component.serials) component.serials = [];
                            component.serials[i] = this.value;
                        });

                        serialDiv.appendChild(serialInput);
                        serialsContainer.appendChild(serialDiv);
                    }
                } else {
                    // For quantity = 1, show single serial input
                    const serialInput = document.createElement('input');
                    serialInput.type = 'text';
                    serialInput.name = `components[${index}][serial]`;
                    serialInput.value = component.serial || (component.serials && component.serials[0] || '');
                    serialInput.placeholder = 'Nhập serial';
                    serialInput.className =
                        'w-full border border-gray-300 rounded-lg px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500';

                    // Save serial when typing
                    serialInput.addEventListener('input', function() {
                        component.serial = this.value;
                    });

                    serialsContainer.appendChild(serialInput);
                }

                container.appendChild(serialsContainer);

                // Add label indicating multiple serials if needed
                if (quantity > 1) {
                    const label = document.createElement('div');
                    label.className = 'text-xs text-gray-500 mt-1 serial-count-label';
                    label.textContent = `${quantity} serial cho linh kiện này`;
                    container.appendChild(label);
                }
            }

            // Add selected component function
            function addSelectedComponent() {
                if (selectedMaterial) {
                    // Check if already added
                    const existingComponent = selectedComponents.find(c => c.id == selectedMaterial.id);
                    if (existingComponent) {
                        // Allow updating existing components with validation
                        const newQty = parseInt(componentAddQuantity.value) || 1;
                        existingComponent.quantity = newQty;

                        // If the component was already in the assembly, check for increased quantity
                        if (existingComponent.isExisting) {
                            if (newQty > existingComponent.originalQuantity) {
                                // Validate stock for the increased amount
                                if (!checkStockSufficiency(existingComponent)) {
                                    alert('Không đủ tồn kho cho số lượng mới!');
                                    existingComponent.quantity = existingComponent.originalQuantity;
                                }
                            }
                        }

                        updateComponentList();
                        componentSearchInput.value = '';
                        componentAddQuantity.value = '1';
                        selectedMaterial = null;
                        searchResults.classList.add('hidden');
                        return;
                    }

                    // Số lượng linh kiện cho mỗi thành phẩm
                    const componentQtyPerProduct = parseInt(componentAddQuantity.value) || 1;

                    // Số lượng thành phẩm
                    const productQty = parseInt(productQuantityInput.value) || 1;

                    // Tổng số linh kiện cần = số lượng thành phẩm * số lượng linh kiện cho mỗi thành phẩm
                    const totalRequiredQty = productQty * componentQtyPerProduct;

                    // Tồn kho hiện có
                    const stockQty = parseInt(selectedMaterial.stock_quantity) || 0;

                    // Check if there's enough stock
                    if (totalRequiredQty > stockQty) {
                        alert(
                            `Không đủ tồn kho! Tồn kho hiện tại: ${stockQty}, Yêu cầu: ${totalRequiredQty} (${productQty} thành phẩm × ${componentQtyPerProduct} linh kiện/thành phẩm)`
                        );
                        return;
                    }

                    // Add to selected components
                    const newComponent = {
                        id: selectedMaterial.id,
                        code: selectedMaterial.code,
                        name: selectedMaterial.name,
                        type: selectedMaterial.category || '',
                        quantity: componentQtyPerProduct,
                        originalQuantity: componentQtyPerProduct, // Store the original quantity for validation
                        stock_quantity: selectedMaterial.stock_quantity || 0,
                        serial: selectedMaterial.serial || '',
                        serials: [],
                        note: '',
                        manuallyAdjusted: true, // Mark as manually adjusted to prevent auto-update from product quantity
                        isExisting: false // This is a newly added component
                    };

                    // Check stock sufficiency
                    checkStockSufficiency(newComponent);

                    selectedComponents.push(newComponent);

                    // Update UI
                    updateComponentList();
                    componentSearchInput.value = '';
                    componentAddQuantity.value = '1'; // Reset quantity to 1
                    selectedMaterial = null;
                    searchResults.classList.add('hidden');
                } else {
                    const searchTerm = componentSearchInput.value.trim();

                    if (!searchTerm) {
                        alert('Vui lòng chọn linh kiện trước khi thêm!');
                        return;
                    }

                    // Không tìm thấy linh kiện hoặc chưa chọn
                    alert('Vui lòng chọn một linh kiện từ danh sách!');
                }
            }

            // Update stock warning when quantity changes
            function updateStockWarning(row, component) {
                const stockWarningContainer = row.querySelector('td:nth-child(4) div');

                if (component.stockWarning) {
                    if (stockWarningContainer) {
                        stockWarningContainer.innerHTML = component.stockWarning;
                    } else {
                        const warningDiv = document.createElement('div');
                        warningDiv.className = 'text-xs text-red-600 font-medium mt-1';
                        warningDiv.textContent = component.stockWarning;
                        row.querySelector('td:nth-child(4)').appendChild(warningDiv);
                    }
                } else if (stockWarningContainer) {
                    stockWarningContainer.remove();
                }
            }

            // Cập nhật danh sách linh kiện
            function updateComponentList() {
                // Ẩn thông báo "không có linh kiện"
                if (selectedComponents.length > 0) {
                    noComponentsRow.style.display = 'none';
                } else {
                    noComponentsRow.style.display = '';
                }

                // Xóa các hàng linh kiện hiện tại (trừ hàng thông báo)
                const componentRows = document.querySelectorAll('.component-row');
                componentRows.forEach(row => row.remove());

                // Thêm hàng cho mỗi linh kiện đã chọn
                selectedComponents.forEach((component, index) => {
                    const row = document.createElement('tr');
                    row.className = 'component-row bg-white hover:bg-gray-50';

                    // Hiển thị cảnh báo tồn kho nếu có
                    const stockWarningHtml = component.stockWarning ?
                        `<div class="text-xs text-red-600 font-medium mt-1">${component.stockWarning}</div>` :
                        '';

                    row.innerHTML = `
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <input type="hidden" name="components[${index}][id]" value="${component.id}">
                            ${component.code}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            ${component.type || ''}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">${component.name}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            <input type="number" min="1" name="components[${index}][quantity]" value="${component.quantity}"
                                class="w-20 border border-gray-300 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500 quantity-input">
                            ${stockWarningHtml}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 serial-cell">
                            <!-- Serial inputs will be added here -->
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            <input type="text" name="components[${index}][note]" value="${component.note || ''}"
                                class="w-full border border-gray-300 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Ghi chú">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button type="button" class="text-red-500 hover:text-red-700 delete-component" data-index="${index}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    `;

                    componentList.insertBefore(row, noComponentsRow);

                    // Generate serial inputs based on quantity
                    const serialCell = row.querySelector('.serial-cell');
                    generateSerialInputs(component, index, serialCell);

                    // Add event listener for quantity change
                    const quantityInput = row.querySelector('.quantity-input');
                    quantityInput.addEventListener('change', function() {
                        const newQty = parseInt(this.value) || 1;
                        if (newQty < 1) {
                            this.value = component.quantity = 1;
                        } else {
                            component.quantity = newQty;
                        }

                        // Mark as manually adjusted
                        component.manuallyAdjusted = true;

                        // Check if quantity differs from original formula
                        checkAndShowCreateNewProductButton();

                        // Update serial inputs
                        generateSerialInputs(component, index, serialCell);

                        // Check stock sufficiency and update warning
                        checkStockSufficiency(component);
                        updateStockWarning(row, component);
                    });

                    // Thêm event listener để xóa linh kiện
                    row.querySelector('.delete-component').addEventListener('click', function() {
                        selectedComponents.splice(index, 1);
                        updateComponentList();
                    });
                });

                // Check and show create new product button after updating components
                checkAndShowCreateNewProductButton();
            }

            // Validation trước khi submit
            document.querySelector('form').addEventListener('submit', function(e) {
                // Kiểm tra kho xuất và kho nhập
                if (!validateWarehouses()) {
                    e.preventDefault();
                    alert('Kho nhập thành phẩm phải khác với kho xuất linh kiện!');
                    return false;
                }

                if (selectedComponents.length === 0) {
                    e.preventDefault();
                    alert('Vui lòng thêm ít nhất một vật tư vào phiếu lắp ráp!');
                    return false;
                }

                                        // Kiểm tra serial thành phẩm (nếu có)
                        const serialInputs = document.querySelectorAll('input[name*="serials"]');
                        let hasSerialError = false;
                        let hasDuplicateSerials = false;
                        let serialValues = [];

                        // Kiểm tra các trường serial có lỗi
                        serialInputs.forEach(input => {
                            if (input.classList.contains('border-red-500')) {
                                hasSerialError = true;
                            }

                            if (input.value.trim()) {
                                // Kiểm tra trùng lặp
                                if (serialValues.includes(input.value.trim())) {
                                    hasDuplicateSerials = true;
                                } else {
                                    serialValues.push(input.value.trim());
                                }
                            }
                        });

                        if (hasSerialError) {
                            e.preventDefault();
                            alert('Vui lòng kiểm tra lại các serial có lỗi!');
                            return false;
                        }

                        if (hasDuplicateSerials) {
                            e.preventDefault();
                            alert('Phát hiện trùng lặp serial. Vui lòng kiểm tra lại!');
                            return false;
                        }

                // Kiểm tra số lượng và tồn kho
                let hasStockError = false;
                let errorMessages = [];

                // Kiểm tra số lượng vật tư
                for (const component of selectedComponents) {
                    if (parseInt(component.quantity) < 1) {
                        e.preventDefault();
                        alert('Số lượng vật tư phải lớn hơn 0!');
                        return false;
                    }

                    // Kiểm tra lại tồn kho
                    checkStockSufficiency(component);
                    if (!component.isStockSufficient) {
                        hasStockError = true;
                        errorMessages.push(
                            `- ${component.code}: ${component.name} - ${component.stockWarning}`
                        );
                    }
                }

                // Nếu có lỗi tồn kho, hiển thị thông báo và ngăn submit form
                if (hasStockError) {
                    e.preventDefault();
                    alert(`Không thể lưu phiếu lắp ráp do không đủ tồn kho:\n${
                        errorMessages.join('\n')
                    }`);
                    return false;
                }

                return true;
            });

            // Khởi tạo: tải danh sách linh kiện của kho nếu đã chọn kho
            if (warehouseSelect.value) {
                fetchWarehouseMaterials(warehouseSelect.value);
            }

            // Note: Edit mode uses existing product serial inputs from the HTML template

            // Note: Serial validation functions removed for edit mode since we use simple inputs

            // Function to check if any components have modified quantities
            function checkComponentsModified() {
                console.log('Checking modified components');
                console.log('Selected components:', selectedComponents);
                
                const hasModified = selectedComponents.some(component => {
                    const isModified = component.quantity !== component.originalQuantity;
                    console.log(`Component ${component.name}: current=${component.quantity}, original=${component.originalQuantity}, modified=${isModified}`);
                    return isModified;
                });
                
                console.log('Has modified components:', hasModified);
                return hasModified;
            }

            // Function to add the "Create New Product" button
            function addCreateNewProductButton() {
                // Look for existing component table container
                const componentContainer = document.querySelector('.component-container') || componentList.parentElement;
                if (!componentContainer) return;

                // Remove existing button if any
                const existingSection = componentContainer.querySelector('.duplicate-section');
                if (existingSection) {
                    existingSection.remove();
                }

                const duplicateSection = document.createElement('div');
                duplicateSection.className = 'bg-yellow-50 border border-yellow-200 rounded-lg p-4 mt-4 duplicate-section';
                duplicateSection.innerHTML = `
                    <div class="flex justify-between items-center">
                        <div class="text-sm text-yellow-700">
                            <i class="fas fa-info-circle mr-2"></i>
                            Bạn đã thay đổi công thức gốc. Bạn có thể tạo một thành phẩm mới với công thức này.
                        </div>
                        <button type="button" class="create-new-product-btn bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded-md text-sm">
                            <i class="fas fa-plus-circle mr-1"></i> Tạo thành phẩm mới
                        </button>
                    </div>
                `;
                
                // Insert after the component list container
                componentContainer.appendChild(duplicateSection);
                
                // Add event listener to the create new product button
                setTimeout(() => {
                    const createNewBtn = duplicateSection.querySelector('.create-new-product-btn');
                    if (createNewBtn) {
                        createNewBtn.addEventListener('click', function() {
                            showCreateNewProductModal();
                        });
                    }
                }, 100);
            }

            // Function to check and show create new product button
            function checkAndShowCreateNewProductButton() {
                console.log('checkAndShowCreateNewProductButton called');
                
                const isModified = checkComponentsModified();
                console.log('IsModified:', isModified);

                if (isModified) {
                    console.log('Adding create new product button');
                    addCreateNewProductButton();
                } else {
                    console.log('Removing create new product button');
                    // Remove the button if no longer modified
                    const existingSection = document.querySelector('.duplicate-section');
                    if (existingSection) {
                        existingSection.remove();
                    }
                }
            }

            // Function to show create new product modal/alert
            function showCreateNewProductModal() {
                const productName = productSelect.options[productSelect.selectedIndex].text;
                
                if (selectedComponents.length === 0) return;

                // Create a summary of the modified formula
                let formulaSummary = 'Công thức mới:\n';
                selectedComponents.forEach(comp => {
                    const isModified = comp.quantity !== comp.originalQuantity;
                    const status = isModified ? ` (đã thay đổi từ ${comp.originalQuantity})` : '';
                    formulaSummary += `- ${comp.name}: ${comp.quantity}${status}\n`;
                });

                // Show confirmation dialog
                const confirmed = confirm(
                    `Bạn có muốn tạo thành phẩm mới "${productName} (Modified)" với công thức sau?\n\n${formulaSummary}\n` +
                    `Chức năng này sẽ lưu công thức mới vào hệ thống để sử dụng cho các lần lắp ráp tiếp theo.`
                );
            }

            // Purpose selection handler
            const purposeSelect = document.getElementById('purpose');
            const projectSelection = document.getElementById('project_selection');
            const projectIdSelect = document.getElementById('project_id');
            const targetWarehouseContainer = document.getElementById('target_warehouse_container');
            const targetWarehouseSelect = document.getElementById('target_warehouse_id');

            purposeSelect.addEventListener('change', function() {
                if (this.value === 'project') {
                    // Chọn "Xuất đi dự án" -> Ẩn kho nhập, hiện dự án
                    targetWarehouseContainer.style.display = 'none';
                    targetWarehouseSelect.removeAttribute('required');
                    
                    projectSelection.classList.remove('hidden');
                    projectSelection.style.display = 'block';
                    projectIdSelect.setAttribute('required', 'required');
                } else {
                    // Chọn "Lưu kho" -> Hiện kho nhập, ẩn dự án
                    targetWarehouseContainer.style.display = 'block';
                    targetWarehouseSelect.setAttribute('required', 'required');
                    
                    projectSelection.classList.add('hidden');
                    projectSelection.style.display = 'none';
                    projectIdSelect.removeAttribute('required');
                }
            });

            // Khởi tạo trạng thái ban đầu dựa trên giá trị hiện tại
            if (purposeSelect.value === 'project') {
                targetWarehouseContainer.style.display = 'none';
                targetWarehouseSelect.removeAttribute('required');
                projectSelection.classList.remove('hidden');
                projectSelection.style.display = 'block';
                projectIdSelect.setAttribute('required', 'required');
            } else {
                targetWarehouseContainer.style.display = 'block';
                targetWarehouseSelect.setAttribute('required', 'required');
                projectSelection.classList.add('hidden');
                projectSelection.style.display = 'none';
                projectIdSelect.removeAttribute('required');
            }
        });
    </script>
</body>

</html>
