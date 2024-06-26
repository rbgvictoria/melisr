<?php
    $config = array(
        array( // standard printer
            array(// standard label [1]
                'numx'  =>  2,
                'numy'  =>  2,
                'barcode'   =>  true,
                'footerpositionabsolute'    =>  true,
                'footeroffsety' => 25,
                'wheader' => 87,
                'xbarcode'  =>  44.5,
                'ybarcode'  =>  18,
                'ybarcodetext'  =>  29,
                'whtml' =>  86.5,
                'yhtml' =>  35,
            ),
            array(// standard label long [2]
                'numx'  =>  2,
                'numy'  =>  1,
                'barcode'   =>  true,
                'footerpositionabsolute'    =>  false,
                'wheader' => 87,
                'xbarcode'  =>  44.5,
                'ybarcode'  =>  18,
                'ybarcodetext'  =>  29,
                'whtml' =>  86.5,
                'yhtml' =>  35
            ),
            array(// crowded sheet label [3]
                'numx'  =>  2,
                'numy'  =>  2,
                'barcode'   =>  true,
                'footerpositionabsolute'    =>  false,
                'wheader' => 87,
                'xbarcode'  =>  44.5,
                'ybarcode'  =>  18,
                'ybarcodetext'  =>  29,
                'whtml' =>  86.5,
                'yhtml' =>  35,
            ),
            array(// bryophyte label, 3 per page [4]
                'numx'  =>  1,
                'numy'  =>  3,
                'barcode'   =>  true,
                'footerpositionabsolute'    =>  true,
                'footeroffsety' => 15,
                'wheader'   =>  165,
                'yheader' => 10,
                'xbarcode'  =>  147.5,
                'ybarcode'  =>  9,
                'ybarcodetext'  =>  20,
                'whtml' =>  190,
                'yhtml' =>  28,
                'xpos' => 10,
            ),
            array(// bryophyte label, 2 per page [5]
                'numx'  =>  1,
                'numy'  =>  2,
                'barcode'   =>  true,
                'footerpositionabsolute'    =>  false,
                'wheader'   =>  160,
                'xbarcode'  =>  152.5,
                'ybarcode'  =>  6,
                'ybarcodetext'  =>  17,
                'whtml' =>  195,
                'yhtml' =>  25
            ),
            array(// duplicate label [6]
                'numx'  =>  2,
                'numy'  =>  2,
                'barcode'   =>  true,
                'footerpositionabsolute'    =>  true,
                'wheader' => 87,
                'xbarcode'  =>  44.5,
                'ybarcode'  =>  125,
                'ybarcodetext'  =>  136,
                'whtml' =>  86.5,
                'yhtml' =>  20
            ),
            array(// duplicate label long [7]
                'numx'  =>  2,
                'numy'  =>  1,
                'barcode'   =>  true,
                'footerpositionabsolute'    =>  false,
                'wheader' => 87,
                'xbarcode'  =>  44.5,
                'ybarcode'  =>  120,
                'ybarcodetext'  =>  131,
                'whtml' =>  86.5,
                'yhtml' =>  20
            ),
            array(// spirit jar label [8]
                'numx'  =>  3,
                'numy'  =>  10,
                'labelheight' => 26.7,
                'labelwidth' => 67.8,
                'wheader' => 56,
                'yheader' => 20,
                'whtml' =>  56,
                'yhtml' =>  24,
                'xpos'  =>  9,
            ),
            array(// multisheet label [9]
                'numx'  =>  3,
                'numy'  =>  10,
                'labelheight' => 26.7,
                'labelwidth' => 67.8,
                'wheader' => 56,
                'yheader' => 20,
                'whtml' =>  56,
                'yhtml' =>  24,
                'xpos'  =>  9,
            ),
            array(// type folder label [10]
                'numx'  =>  1,
                'numy'  =>  12,
                'wheader' => FALSE,
                'yheader' => FALSE,
                'barcode' => true,
                'whtml' =>  190.5,
                'yhtml' =>  8,
                'xpos'  =>  10
            ),
            array(// barcode label[11]
                'numx'  =>  3,
                'numy'  =>  10,
                'wheader' => FALSE,
                'yheader' => FALSE,
                'barcode' => true,
                'labelheight' => 26.7,
                'labelwidth' => 67.2,
                'xbarcode'  =>  10,
                'ybarcode'  =>  20,
                'ybarcodetext'  =>  31,
                'whtml' =>  66,
                'yhtml' =>  20,
                'xpos'  =>  15,
            ),
            array(// spirit card [12]
                'numx'  =>  1,
                'numy'  =>  1,
                'orientation' => 'L',
                'barcode'   =>  true,
                'footerpositionabsolute'    =>  true,
                'footeroffsety' => 12,
                'wheader' => 87,
                'xbarcode'  =>  236.5,
                'ybarcode'  =>  13,
                'ybarcodetext'  =>  24,
                'whtml' =>  129.5,
                'yhtml' =>  22,
                'yheader' => 7.5,
                'xpos' => 152.5,
                'spiritinfo' => true,
                'width' => 150,
                'height' => 103,
            ),
            array(// seedbank duplicate label [13]
                'numx'  =>  2,
                'numy'  =>  2,
                'barcode'   =>  true,
                'footerpositionabsolute'    =>  true,
                'wheader' => 87,
                'xbarcode'  =>  44.5,
                'ybarcode'  =>  120,
                'ybarcodetext'  =>  131,
                'whtml' =>  86.5,
                'yhtml' =>  35
            ),
            array(// seedbank duplicate label long [14]
                'numx'  =>  2,
                'numy'  =>  1,
                'barcode'   =>  true,
                'footerpositionabsolute'    =>  false,
                'wheader' => 87,
                'xbarcode'  =>  44.5,
                'ybarcode'  =>  120,
                'ybarcodetext'  =>  131,
                'whtml' =>  86.5,
                'yhtml' =>  35
            ),
            array(// annotation label [15]
                'numx'  =>  3,
                'numy'  =>  10,
                'labelheight' => 26.7,
                'labelwidth' => 67.8,
                'wheader' => 56,
                'yheader' => 17,
                'whtml' =>  56,
                'yhtml' =>  21,
                'xpos'  =>  9,
            ),
            array(// annotation label with det. notes [16]
                'numx'  =>  3,
                'numy'  =>  10,
                'labelheight' => 26.7,
                'labelwidth' => 67.8,
                'wheader' => 60,
                'yheader' => 11,
                'whtml' =>  60,
                'yhtml' =>  15,
                'xpos'  =>  6,
            ),
            array(// carpol. card [17]
                'numx'  =>  1,
                'numy'  =>  1,
                'orientation' => 'L',
                'barcode'   =>  true,
                'footerpositionabsolute'    =>  true,
                'footeroffsety' => 12,
                'wheader' => 127,
                'xbarcode'  =>  233.5,
                'ybarcode'  =>  7,
                'ybarcodetext'  =>  18,
                'whtml' =>  178,
                'yhtml' =>  22,
                'yheader' => 7.5,
                'xpos' => 99,
                'height' => 127,
                'width' => 203,
            ),
            array(// silica gel sample label [18]
                'numx'  =>  3,
                'numy'  =>  10,
                'labelheight' => 26.7,
                'labelwidth' => 67.8,
                'wheader' => 56,
                'yheader' => 20,
                'whtml' =>  56,
                'yhtml' =>  24,
                'xpos'  =>  9,
            ),
            array(// VRS label [19]
                'numx'  =>  2,
                'numy'  =>  2,
                'barcode'   =>  true,
                'footerpositionabsolute'    =>  true,
                'footeroffsety' => 25,
                'wheader' => 87,
                'xbarcode'  =>  44.5,
                'ybarcode'  =>  20,
                'ybarcodetext'  =>  31,
                'whtml' =>  86.5,
                'yhtml' =>  35,
            ),
            array(// VRS barcode label[20]
                'numx'  =>  3,
                'numy'  =>  10,
                'wheader' => FALSE,
                'yheader' => FALSE,
                'barcode' => true,
                'labelheight' => 26.7,
                'labelwidth' => 67.2,
                'xbarcode'  =>  10,
                'ybarcode'  =>  20,
                'ybarcodetext'  =>  31,
                'whtml' =>  66,
                'yhtml' =>  20,
                'xpos'  =>  15,
            ),
            array(// VRS label long [21]
                'numx'  =>  2,
                'numy'  =>  1,
                'barcode'   =>  true,
                'footerpositionabsolute'    =>  false,
                'wheader' => 87,
                'xbarcode'  =>  44.5,
                'ybarcode'  =>  20,
                'ybarcodetext'  =>  31,
                'whtml' =>  86.5,
                'yhtml' =>  35
            ),
            array(// spirit card (mail area printer) [22]
                'numx'  =>  1,
                'numy'  =>  1,
                'format' => array(150, 103),
                'orientation' => 'L',
                'barcode'   =>  true,
                'footerpositionabsolute'    =>  true,
                'footeroffsety' => 6,
                'wheader' => 87,
                'xbarcode'  =>  90, //81.5,
                'ybarcode'  =>  10.5, //13,
                'ybarcodetext'  =>  21.5, //24,
                'whtml' =>  140, //129.5,
                'yhtml' =>  19.5, //22,
                'yheader' => 5, //7.5,
                'xpos' => 7.5,
                'spiritinfo' => true,
                'width' => 150,
                'height' => 103,
            ),
            array(// carpol. card (mail area printer) [23]
                'numx'  =>  1,
                'numy'  =>  1,
                'format' => array(127, 203),
                'orientation' => 'L',
                'barcode'   =>  true,
                'footerpositionabsolute'    =>  true,
                'footeroffsety' => 7,
                'wheader' => 127,
                'xbarcode'  =>  142, //233.5,
                'ybarcode'  =>  7,
                'ybarcodetext'  =>  18,
                'whtml' =>  178,
                'yhtml' =>  22,
                'yheader' => 7.5,
                'xpos' => 7.5, //99,
                'height' => 127,
                'width' => 203,
            ),
            array(// multisheet label Vic. Ref. Set [24]
                'numx'  =>  3,
                'numy'  =>  10,
                'labelheight' => 26.7,
                'labelwidth' => 67.8,
                'wheader' => 56,
                'yheader' => 20,
                'whtml' =>  56,
                'yhtml' =>  24,
                'xpos'  =>  9,
            ),
            array(// type annotation label [25]
                'numx'  =>  3,
                'numy'  =>  10,
                'labelheight' => 26.7,
                'labelwidth' => 67.8,
                'wheader' => 56,
                'yheader' => 17,
                'whtml' =>  56,
                'yhtml' =>  21,
                'xpos'  =>  9,
            ),
            array(// carpological collection label [26]
                'numx'  =>  3,
                'numy'  =>  10,
                'labelheight' => 26.7,
                'labelwidth' => 67.8,
                'wheader' => 56,
                'yheader' => 20,
                'whtml' =>  56,
                'yhtml' =>  24,
                'xpos'  =>  9,
            ),
       )
    );

