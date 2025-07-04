<?php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "materials_db";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Fetch all tables
    $tables = $conn->query("SHOW TABLES");

    if (!$tables) {
        die("Error fetching tables: " . $conn->error);
    }

    // Get the timestamp from the POST data
    $timestamp = isset($_POST['timestamp']) ? $_POST['timestamp'] : date('Y-m-d_H_i');
    $filename = $timestamp . '_Data.xlsx';

    // Create a new Spreadsheet object
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Write title row
    $sheet->setCellValue('A1', 'Material Monitoring System - JIG');
    $sheet->mergeCells('A1:H1');
    $sheet->getStyle('A1')->getFont()->setBold(true);
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    // Add an empty row for spacing
    $sheet->setCellValue('A2', '');

    // Write CSV header
    $header = ['Table Name', 'id of row', '....', 'Datetime', 'Quantity In', 'Quantity Out', 'Person In Charge', 'Total Balance Quantity'];
    $sheet->fromArray($header, NULL, 'A3');
    
    // Style the header
    $headerRange = 'A3:H3';
    $sheet->getStyle($headerRange)->getFont()->setBold(true);
    $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->setStartColor(new Color('D3D3D3')); // Light Gray

    // Set column widths
    foreach (range('A', 'H') as $columnID) {
        $sheet->getColumnDimension($columnID)->setWidth(20);
    }

    // Loop through each table
    $rowCount = 4; // Start writing data from row 4
    while ($table = $tables->fetch_array()) {
        $tableName = $table[0];

        // Find the primary key column or appropriate column for sorting
        $columns = $conn->query("SHOW COLUMNS FROM $tableName");
        $primaryKeyColumn = null;

        while ($column = $columns->fetch_assoc()) {
            if ($column['Key'] === 'PRI') {
                $primaryKeyColumn = $column['Field'];
                break;
            }
        }

        if ($primaryKeyColumn) {
            // Fetch the last row from the table
            $query = "SELECT * FROM $tableName ORDER BY $primaryKeyColumn DESC LIMIT 1";
            $result = $conn->query($query);

            if (!$result) {
                echo "Error executing query for table $tableName: " . $conn->error . "<br>";
                continue; // Skip to the next table
            }

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                // Prepare data for the spreadsheet
                $excelRow = array_merge([$tableName], array_values($row));
                $sheet->fromArray($excelRow, NULL, 'A' . $rowCount);
                $rowCount++;
            }
        } else {
            echo "No primary key found for table $tableName.<br>";
        }
    }

    // Create a Writer instance and save the spreadsheet
    $writer = new Xlsx($spreadsheet);

    // Set headers and output the file
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    $writer->save('php://output');
    exit();
}
?>




<!-- HTML for User Interface and Design -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> 
    <link rel="icon" type="image/favicon" href="img/hayakawalogo.png" sizes="any"/>
    <style>
        body {
    font-family: 'Roboto', sans-serif; 
    margin: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh; 
    overflow: hidden; 
    background: linear-gradient(51deg, rgba(2,0,36,1) 0%, rgba(36,52,60,0.2205933988764045) 35%, rgba(0,212,255,1) 100%); 
}

.main {
    text-align: center;
    position: relative;
    max-width: 50%; 
    padding: 20px;    
}

.title {
    font-size: 2rem;
    text-align: center;
    margin: 20px 0;
    color: #000;
    font-family: 'Orbitron', sans-serif; 
}

.search-container {
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: #fff; 
    border-radius: 24px; 
    padding: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    position: relative; 
    width: 100%;
}

.search-container i {
    position: absolute;
    left: 4%; 
    font-size: 18px;
    color: #aaa; 
    pointer-events: none; 
    font-size: 15px;
}

.search-container input {
    width: 100%;
    padding: 12px 12px 12px 35px; 
    border: 1px solid #ccc;
    border-radius: 24px;
    outline: none;
    font-size: 16px;
    box-sizing: border-box; 
    padding-left: 6%; 
}

.search-container input::placeholder {
    color: #aaa; 
    padding-left: 1%;
}

.search-container input:focus {
    border-color: #000; 
}

.search-container button {
    padding: 12px 24px;
    margin-left: 10px;
    background-color: #000;
    color: white;
    border: none;
    border-radius: 24px;
    cursor: pointer;
    font-size: 16px;
    font-family: 'Orbitron', sans-serif; 
}

.search-container button:hover {
    background-color: white; 
    color: black;
    border: 1px solid black;
}

.suggestions {
    position: absolute;
    top: 100%;
    left: 0;
    width: 100%;
    border: 1px solid #ccc;
    background-color: white;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    display: none;
    max-height: 200px;
    overflow-y: auto;
    border-radius: 8px;
    z-index: 10; 
}

.suggestion-item {
    padding: 8px;
    cursor: pointer;
}

.suggestion-item:hover {
    background-color: #f0f0f0;
}

.container {
    width: 80%;
    max-width: 1200px;
    margin: auto;
    background-color: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    position: relative;
}

.btn-download {
    background-color: #228B22; 
    color: white;
    border: none;
    padding: 10px 20px;
    text-transform: uppercase;
    cursor: pointer;
    font-size: 500px;
    font-weight: bold;
}

.btn-download:hover {
    background-color: #1e8e1e;
}

footer {
    color: #000;
    text-align: center;
    padding: 10px 20px;
    position: relative;
    width: 100%;
    margin-top: 10%;
}

.footer-content {
    max-width: 1200px;
    margin: auto;
}

.footer a {
    color: #f5f5f5;
    text-decoration: none;
}

.footer-info {
    margin-top: 250px;
}

.footer-title {
    font-size: 18px;
    font-weight: bold;
    margin-bottom: -200;
}

.footer-info p, .footer-credits p {
    margin: 0px 0;
}

.icons img {
    width: 5%;
    padding-top: 25px;
    padding-left: 7%;
    transition: transform 0.3s ease; 
}

.icons img:hover {
    transform: scale(1.2); 
}

.logo {
    top: 100px;
}

.info-icon img {
    width: 30px;
    height: 30px;
    padding-left: 4px;
}

.info-icon img:hover {
    transform: scale(1.2); 
}

    </style>
</head>
<body>

<div class="main">

    <div class="logo">
        <img src="img/hayakawalogo.png" alt="">
    </div>

    <div class="title">Materials Monitoring System</div>

    <div class="search-container">
        <i class="fas fa-search search-icon"></i> <!-- Search icon -->
        <input type="text" id="searchInput" placeholder="Search materials...">
        <button onclick="performSearch()">Search</button>
        <div id="suggestions" class="suggestions"></div>
        <form id="exportForm" action="SearchBar.php" method="post">
            <input type="hidden" name="timestamp" id="timestamp">
            <button type="submit" class="btn-download"> ⤓ </button>
        </form>
        <div class="info-icon">
            <a href="info.html"><img src="img/info.png" alt="info"></a>
        </div>
    </div>

    <div class="icons">
        <a><img src="img/resistor.png" alt="resistor"></a>
        <a><img src="img/chip.png" alt="memory"></a>
        <a><img src="img/led.png" alt="led"></a>
        <a><img src="img/cabinet.png" alt="cabinet"></a>
        <a><img src="img/plug.png" alt="plug"></a>
        <a><img src="img/transistor.png" alt="transistor"></a>
        <a><img src="img/wire.png" alt="wire"></a>
    </div>

    <footer>
        <div class="footer-container">
            <div class="footer-info">
                <p class="footer-title">Developer Team</p>
                <p>Justen Ligutan Nicanor</p>
                <p>Idealist: Raven Del Rosario</p>
                <p>Support: D.I Jubilo & C.J Zoleta</p>
                <p>&copy; Polytechnic University of the Philippines - Manila, 2024</p>
            </div>
        
        </div>
    </footer>
</div>

<!-- JavaScript for Data Handling -->

<script>
    const materials = {
        '1Admin': '1Admin.php',
        'Resistor': 'resistor.php',
        '3S Bottom': '3s_bottom.php',
        '4S Bottom': '4s_bottom.php',
        'Accessories Checker': 'accessories_checker.php',
        'Acetal White': 'acetal_white.php',
        'Acrylic': 'acrylic.php',
        'Air Gripper Cylinder': 'air_gripper_cylinder.php',
        'Transistor': 'transistor.php',
        'Blade': 'blade.php',
        'Plastic Hinge': 'plastic_hinge.php',
        'Cable Tray': 'cable_tray.php',
        'Metal Joint': 'metal_joint.php',
        'Binder': 'binder.php',
        'Spiral Tube': 'spiral_tube.php',
        'Relay Module': 'relay_module.php',
        'Tower light': 'tower_light.php',
        'Auto Alarm NGB PLC Type': 'auto_alarm_ngb_plc_type.php',
        'Heat Gun': 'heat_gun.php',
        'Aluminum End Cap': 'aluminum_end_cap.php',
        'Ply Board': 'ply_board.php',
        'Air Regulator': 'air_regulator.php',
        'Fittings': 'fittings.php',
        'Screw Driver': 'screw_driver.php',
        'Tailin Super Thin Cutting Disc 4': 'tailin_super_thin_cutting_disc_4.php',
        'Block Box': 'block_box.php',
        'Micro Switch P336-01': 'micro_switch.php',
        'Stainless Allen Bolt and Nut with Washer': 'stainless_allen_bolt_and_nut_with_washer.php',
        'Universal Car Fuel Pump': 'universal_car_fuel_pump.php',
        'Aluminum Profile': 'aluminum_profile.php',
        'Spray Paint': 'spray_paint.php',
        'Corner Bracket': 'corner_bracket.php',
        'LED Green Super Bright': 'led_green_super_bright.php',
        'Fuse Holder': 'fuse_holder.php',
        'Fuse Glass': 'fuse_glass.php',
        'Universal PCB': 'universal_pcb.php',
        'Opto Coupler': 'opto_coupler.php',
        'Bearing M5': 'bearing.php',
        'Plug': 'plug.php',
        'Linear Guide': 'linear_guide.php',
        'Stanley Steel Tape': 'stanley_steel_tape.php',
        'Stanley Screwdriver': 'stanley_screwdriver.php',
        'Polycarbonate': 'polycarbonate.php',
        'Bar Cutter': 'bar_cutter.php',
        'Manual Cutting Jig': 'manual_cutting_jig.php',
        'Drill Bit': 'drill_bit.php',
        'Channel Relay Module': 'channel_relay_module.php',
        'Solenoid': 'solenoid.php',
        'Fittings Speed Controller': 'fittings_speed_controller.php',
        'Ivory Pipe': 'ivory_pipe.php',
        'Printer Cable 1.5M': 'printer_cable_1.5m.php',
        'Screwdriver Holder Adapter': 'screwdriver_holder_adapter.php',
        'Jigsaw Blade': 'jigsaw_blade.php',
        'Mini Bolt Cutter Blade': 'mini_bolt_cutter_blade.php',
        'Stainless Allen Bolt M6x35mm': 'stainless_allen_bolt.php',
        'Keyence Camera': 'keyence_camera.php',
        'Panasonic': 'panasonic.php',
        'Power Supply': 'power_supply.php',
        'Solenoid Valve': 'solenoid_valve.php',
        'Cylinder': 'cylinder.php',
        'Alexan Box': 'alexan_box.php',
        'Mega 2560': 'mega_2560.php',
        'TFT Touch Screen': 'tft_touch_screen.php',
        'IC Socket': 'ic_socket.php',
        'Relay': 'relay.php',
        'Spring for Wire Gauge 18': 'spring_for_wire_gauge_18.php',
        'Spring for Wire Gauge 26': 'spring_for_wire_gauge_26.php',
        'On/Off 12V-250V': 'on_slash_off_12v_to_250v.php',
        'CNC Lathe Tool Holder': 'cnc_lathe_tool_holder.php',
        'Fitting Pneumatic Air Connector': 'fitting_pneumatic_air_connector.php',
        'Solenoid Magnetic': 'solenoid_magnetic.php',
        'Electrical Metal Cabinet': 'electrical_metal_cabinet.php',
        'MF45 Synchronous Belt Linear Slide': 'mf45_synchronous_belt_linear_slide.php',
        'Rail Motion Table Ball Screw Actuator': 'rail_motion_table_ball_screw_actuator.php',
        'Stepper Motor Dirver CNC Controller with Stepper Motor Nema 17 Bipolar 1.7A 40N.CM': 'stepper_motor_driver_cnc_controller_with_stepper_motor_nema_17_bipolar_1.7a_40n.cm.php',
        'PLC Sigma': 'plc_sigma.php',
        'SND4-N Proximity Switch': 'sn04_n_proximity_switch.php',
        'Aluminum Din Rail': 'aluminum_din_rail.php',
        'Nylon Cable Gland (Black Only)': 'nylon_cable_gland_black_only.php',
        'Soldering Iron': 'soldering_iron.php',
        'Aluminum Iron Extruder': 'aluminum_iron_extruder.php',
        'Stepper Motor Driver CNC': 'stepper_motor_driver_cnc.php',
        'Sensor EE-SX670': 'sensor.php',
        'Wood Screw': 'wood_screw.php',
        'Lubeck': 'lubeck.php',
        'CE30-26NO Liquid Sensor': 'ce30-26no_liquid_sensor.php',
        'Aluminum Corner L Bracket Angle L': 'aluminum_corner_l_bracket_angle_l.php',
        'Aluminum Profile Accessories Zinc Pivot Joint': 'aluminum_profile_accessories_zinc_pivot_joint.php',
        'Aluminum Profile 20X40': 'aluminum_profile_20x40.php',
        'Aluminum Corner Bracket Fitting L': 'aluminum_corner_bracket_fitting_l.php',
        'Allen Bolt and Nuts Stainless with Washer': 'allen_bolt_and_nuts_stainless_w_washer.php',
        'T Block Square Nuts': 't_block_square_nuts.php',
        'Filament 1kg': 'filament_1kg.php',
        'ESP32 Development Board': 'esp32_development_board.php',
        'Memory Card': 'memory_card.php',
        'Akusense Sensor': 'akusense_sensor.php',
        'Creality 3D Printer': 'creality_3d_printer.php',
        'Screw Roller Conveyor': 'screw_roller_conveyor.php',
        'Heavy Steel Pipe Cutter': 'heavy_steel_pipe_cutter.php',
        'Character LCD Module Display': 'charcter_lcd_module_display.php',
        'LCD2004 Display Module': 'lcd2004_display_module.php',
        'Filter Bags Vacuum Cleaner': 'filter_bags_vacuum_cleaner.php',
        'Endmill': 'endmill.php',
        'Vision Camera': 'vision_camera.php',
        'Wood Screw Stainless': 'wood_screw_stainless.php',
        'Panasonic PLC Programming Cable USB-AFCB513': 'panasonic_plc_programming_cable_usb_afcb513.php',
        'Cooling Fan': 'cooling_fan.php',
        'Acrylic Chalk White': 'acrylic_chalk_white.php',
        'Metal Steelfile Kikil': 'metal_steelfile_kikil.php',
        'Tox Wall Plug': 'tox_wall_plug.php',
        'Development Board': 'development_board.php',
        'SM Sandpaper Anti-Slip': 'sm_sandpaper_anti_slip.php',
        'Hacksaw Blade': 'hacksaw_blade.php',
        'Omron Switch SS-01GL': 'omron_switch.php',
        'LED Orange Super Bright': 'led_orange_super_bright.php',
        'LED Red Super Bright': 'led_red_super_bright.php',
        'Flat Wire': 'flat_wire.php',
        'Industrial Power Supply': 'industrial_power_supply.php',
        'Returna Keyswitch': 'returna_keyswitch.php',
        'USB Pendrive': 'usb_pendrive.php',
        'Mini Power Bank': 'mini_power_bank.php',
        'PCB C-K4-SMT Connector Conversion': 'pcb_ck4smt_connector_conversion.php',
        'Stainless Steel Butterfly Screw Nut': 'stainless_steel_butterfly_screw_nut.php',
        'Wireless Call Buttons': 'wireless_call_buttons.php',
        '304 Stainless Steel Wire': '304_stainless_steel_wire.php',
        'Shock Absorber for Pneumatic Air': 'shock_absorber_for_pneumatic_air.php',
        'Coupling': 'coupling.php',
        'Stainless Tig Welding Filler Wire Rod': 'stainless_tig_welding_filler_wire_rod.php',
        'TZT Mall Cable Drag Chain': 'tzt_mall_cable_drag_chain.php',
        'Stepper Motor': 'stepper_motor.php',
        'Aluminum Coupler Plum Flexible': 'aluminum_coupler_plum_flexible.php',
        'Splitter': 'splitter.php',
        'Swing Rotating Cylinder HRQ Pneumatic': 'swing_rotating_cylinder_hrq_pneumatic.php',
        'SMC Type Double Shaft Cylinder': 'smc_type_double_shaft_cylinder.php',
        'SMC Magnetic Switch': 'smc_magnetic_switch.php',
        'FP Sigma White 32 1/10': 'fp_sigma_plc_white_32_1_slash_10.php',
        'FP Sigma R5232C Adapter': 'fp_sigma_r5232c_adapter.php',
        'VT3-Keyence Touch Panel': 'vt3_keyence_touch_panel.php',
        'Proximity Switch Metal Detection Sensor': 'proximity_switch_metal_detection_sensor.php',
        'DIN Rail Switching Power Supply': 'din_rail_switching_power_supply.php',
        'Emergency Stop': 'emergency_stop.php',
        'Stepper Motor Driver CNC Controller with Stepper Motor Nema 17 Bipolar 1.7A 40N CM': 'stepper_motor_with_stepper_motor_nema17_bipolar1_dot7a_40ndotcm.php',
        'DIN Rail Switching Power Supply NDR-240 12V 24V': 'din_rail_switching_power_supply_ndr_240_12v_24v.php',
        'R38 Cable Chain': 'r38_cable_chain.php',
        'Aluminum Alloy Extruder': 'aluminum_alloy_extruder.php',
        'CE30-26NO Liquid Sensor': 'ce30_26no_liquid_sensor.php',
        'Black Box': 'black_box.php',
        'Switch': 'switch.php',
        //'Expansion Unit': 'expansion_unit.php', 
        'Mini Buzzer': 'mini_buzzer.php',
        'Tower Light 3 Color 50MM Diameter': 'tower_light_3_color_50mm_diameter.php',
        'PCB': 'pcb.php',
        'Acetal White Aw Sheet': 'acetal_white_aw_sheet.php',
        'Camera Akusense': 'camera_akusense.php',
        'Liquid Sensor': 'liquid_sensor.php',
        'Fiber Optic Front Sight': 'fiber_optic_front_sight.php',
        'Relay 6V/14pins': 'Relay_6v/14pins.php',
        'Plug 220V': 'plug.php',
        'Relay 110/14pins': 'Relay_110.php',
        'Relay 12/14pins': 'Relay_12.php',
        'Transformer Multi': 'transformer_multi.php',
        'Alexan Box HC-873': 'alexan_box_hc873.php',
        'Diode RS205L': 'diode_rs205l.php',
        'Keyswitch Returnable': 'keyswitch.php',
        'Metal Stand Stainless': 'metal_stand.php',
        'Pneumatic Air Hose 6mm': 'pneumatic_6mm.php',
        'Pneumatic Air Hose 4mm': 'pneumatic_4mm.php',
        'IC Socket 16Pins': 'ic_socket_16pins.php',
        'Transistor IRF530N': 'transistor_irf530n.php',
        'Plug 110V': 'plug_110v.php',
        'Blade Ofla': 'blade_ofla.php',
        'Capacitor 1000uf': 'capacitor_1000uf.php',
        'Capacitor 220uf': 'capacitor_220uf.php',
        'Pilot Lamp Green 24v': 'pilot_lamp_green.php',
        'Fuse Glass 1Amp': 'fuse_glass_1amp.php',
        'Transistor A1015 ': 'transistor_a1015.php',
        'Transistor C1815 ': 'transistor_c1815.php',
        'Transistor D669A ': 'transistor_d669a.php',
        'Cable Tray 2x2 ': 'cabletray_2x2.php',
        'Acrylic 6mm ': 'acrylic_6mm.php',
        'Switch D2F-01L3-D21 ': 'switch_d2f.php',
        'Stainless Allen Bolt M5x30mm': 'allen_bolt_m5x30.php',
        'Harness Nail 2.5x55mm ': 'harness_nail_55.php',
        'Harness Nail 2.5x35mm ': 'harness_nail_35.php',
        'Switch On/Off ': 'switch_onoff.php',











      


        




        





        



        // Add more material headers and their corresponding files here

    };

    const searchInput = document.getElementById('searchInput');
    const suggestionsContainer = document.getElementById('suggestions');

    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase();
        suggestionsContainer.innerHTML = '';
        
        if (query) {
            const filteredMaterials = Object.keys(materials).filter(material =>
                material.toLowerCase().includes(query)
            );

            filteredMaterials.forEach(material => {
                const suggestionItem = document.createElement('div');
                suggestionItem.className = 'suggestion-item';
                suggestionItem.textContent = material;
                suggestionItem.addEventListener('click', function() {
                    searchInput.value = material;
                    suggestionsContainer.innerHTML = '';
                    suggestionsContainer.style.display = 'none';
                    navigateToFile(material);
                });
                suggestionsContainer.appendChild(suggestionItem);
            });

            suggestionsContainer.style.display = filteredMaterials.length ? 'block' : 'none';
        } else {
            suggestionsContainer.style.display = 'none';
        }
    });

    function performSearch() {
        const query = searchInput.value.trim();
        if (query && materials[query]) {
            navigateToFile(query);
        }
    }

    function navigateToFile(material) {
        if (materials[material]) {
            window.location.href = materials[material];
        } else {
            alert('No results found');
        }
    }

    // Set the current date and time in the format 'Y-m-d\TH:i' in the hidden input field
    document.addEventListener('DOMContentLoaded', function() {
            var now = new Date();
            var year = now.getFullYear();
            var month = ('0' + (now.getMonth() + 1)).slice(-2);
            var day = ('0' + now.getDate()).slice(-2);
            var hours = ('0' + now.getHours()).slice(-2);
            var minutes = ('0' + now.getMinutes()).slice(-2);
            var timestamp = `${year}-${month}-${day}T${hours}:${minutes}`;
            document.getElementById('timestamp').value = timestamp;
        });
</script>

</body>
</html>
