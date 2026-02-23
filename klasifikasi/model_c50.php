<?php
// Kelas C5.0 untuk klasifikasi stunting 
class C5_0_Classifier {
    private $tree = null; 
    private $accuracy = 0;
    private $rules = [];
    private $koneksi;
    private $targetAttribute = 'status_stunting';
    
    // Definisi interval
    private $ageIntervals = [
        '11-24' => [11, 24],
        '25-36' => [25, 36],
        '37-48' => [37, 48],
        '49-60' => [49, 60]
    ];
    
    private $weightIntervals = [
        '7.5-11.5' => [7.5, 11.5],
        '11.5-15.5' => [11.5, 15.5],
        '15.5-19.5' => [15.5, 19.5],
        '19.5-23.5' => [19.5, 23.5]
    ];
    
    private $heightIntervals = [
        '70-83' => [70, 83],
        '83-96' => [83, 96],
        '96-109' => [96, 109],
        '109-122' => [109, 122]
    ];
    
    private $attributes = ['usia_bulan', 'berat_badan', 'tinggi_badan'];
    
    // Constructor
    public function __construct($koneksi) {
        $this->koneksi = $koneksi;
        $this->tree = null;
        $this->accuracy = 0;
        $this->rules = [];
    }
    
    // Konversi data ke kategori interval
    private function convertToCategorical($row) {
        $categoricalRow = [];

        // Usia Bulan
        $usia = isset($row['usia_bulan']) ? (int)$row['usia_bulan'] : 0;
        if ($usia >= 11 && $usia <= 24) {
            $categoricalRow['usia_bulan'] = '11-24';
        } elseif ($usia >= 25 && $usia <= 36) {
            $categoricalRow['usia_bulan'] = '25-36';
        } elseif ($usia >= 37 && $usia <= 48) {
            $categoricalRow['usia_bulan'] = '37-48';
        } elseif ($usia >= 49 && $usia <= 60) {
            $categoricalRow['usia_bulan'] = '49-60';
        } else {
            $categoricalRow['usia_bulan'] = 'Lainnya';
        }

        // Berat Badan
        $berat = isset($row['berat_badan']) ? (float)$row['berat_badan'] : 0;
        if ($berat >= 7.5 && $berat < 11.5) {
            $categoricalRow['berat_badan'] = '7.5-11.5';
        } elseif ($berat >= 11.5 && $berat < 15.5) {
            $categoricalRow['berat_badan'] = '11.5-15.5';
        } elseif ($berat >= 15.5 && $berat < 19.5) {
            $categoricalRow['berat_badan'] = '15.5-19.5';
        } elseif ($berat >= 19.5 && $berat < 23.5) {
            $categoricalRow['berat_badan'] = '19.5-23.5';
        } else {
            $categoricalRow['berat_badan'] = 'Lainnya';
        }

        // Tinggi Badan
        $tinggi = isset($row['tinggi_badan']) ? (float)$row['tinggi_badan'] : 0;
        if ($tinggi >= 70 && $tinggi < 83) {
            $categoricalRow['tinggi_badan'] = '70-83';
        } elseif ($tinggi >= 83 && $tinggi < 96) {
            $categoricalRow['tinggi_badan'] = '83-96';
        } elseif ($tinggi >= 96 && $tinggi < 109) {
            $categoricalRow['tinggi_badan'] = '96-109';
        } elseif ($tinggi >= 109 && $tinggi < 122) {
            $categoricalRow['tinggi_badan'] = '109-122';
        } else {
            $categoricalRow['tinggi_badan'] = 'Lainnya';
        }

        // Jk
        if (isset($row['jenis_kelamin'])) {
            $categoricalRow['jenis_kelamin'] = $row['jenis_kelamin'];
        }

        // Status Stunting
        if (isset($row['status_stunting'])) {
            $categoricalRow['status_stunting'] = trim($row['status_stunting']);
        }
        
        
        static $debug_count = 0;
        if ($debug_count < 3) {
            error_log("=== KONVERSI C5.0 #" . ($debug_count+1) . " ===");
            error_log("Usia: {$row['usia_bulan']} -> {$categoricalRow['usia_bulan']}");
            error_log("Berat: {$row['berat_badan']} -> {$categoricalRow['berat_badan']}");
            error_log("Tinggi: {$row['tinggi_badan']} -> {$categoricalRow['tinggi_badan']}");
            $debug_count++;
        }
        
        return $categoricalRow;
    }
    
    // Konversi untuk prediksi data baru
    private function convertNewDataToCategorical($data) {
        $categorical = [];

        // Usia Bulan
        $usia = isset($data['usia_bulan']) ? (int)$data['usia_bulan'] : 
               (isset($data['usia']) ? (int)$data['usia'] : 0);
        if ($usia >= 11 && $usia <= 24) {
            $categorical['usia_bulan'] = '11-24';
        } elseif ($usia >= 25 && $usia <= 36) {
            $categorical['usia_bulan'] = '25-36';
        } elseif ($usia >= 37 && $usia <= 48) {
            $categorical['usia_bulan'] = '37-48';
        } elseif ($usia >= 49 && $usia <= 60) {
            $categorical['usia_bulan'] = '49-60';
        } else {
            $categorical['usia_bulan'] = 'Lainnya';
        }

        // Berat Badan
        $berat = isset($data['berat_badan']) ? (float)$data['berat_badan'] : 
                (isset($data['berat']) ? (float)$data['berat'] : 0);
        if ($berat >= 7.5 && $berat < 11.5) {
            $categorical['berat_badan'] = '7.5-11.5';
        } elseif ($berat >= 11.5 && $berat < 15.5) {
            $categorical['berat_badan'] = '11.5-15.5';
        } elseif ($berat >= 15.5 && $berat < 19.5) {
            $categorical['berat_badan'] = '15.5-19.5';
        } elseif ($berat >= 19.5 && $berat < 23.5) {
            $categorical['berat_badan'] = '19.5-23.5';
        } else {
            $categorical['berat_badan'] = 'Lainnya';
        }

        // Tinggi Badan
        $tinggi = isset($data['tinggi_badan']) ? (float)$data['tinggi_badan'] : 
                 (isset($data['tinggi']) ? (float)$data['tinggi'] : 0);
        if ($tinggi >= 70 && $tinggi < 83) {
            $categorical['tinggi_badan'] = '70-83';
        } elseif ($tinggi >= 83 && $tinggi < 96) {
            $categorical['tinggi_badan'] = '83-96';
        } elseif ($tinggi >= 96 && $tinggi < 109) {
            $categorical['tinggi_badan'] = '96-109';
        } elseif ($tinggi >= 109 && $tinggi < 122) {
            $categorical['tinggi_badan'] = '109-122';
        } else {
            $categorical['tinggi_badan'] = 'Lainnya';
        }

        // Jk
        if (isset($data['jenis_kelamin'])) {
            $categorical['jenis_kelamin'] = $data['jenis_kelamin'];
        } elseif (isset($data['jk'])) {
            $categorical['jenis_kelamin'] = $data['jk'];
        }

        return $categorical;
    }
    
    // Training data dari database
    public function trainFromDatabase() {
        try {
            // Ambil data training
            $sql = "SELECT 
                        usia_bulan,
                        jenis_kelamin,
                        berat_badan,
                        tinggi_badan,
                        status_stunting 
                    FROM training_stunting 
                    WHERE tipe_data = 'Training' 
                    AND status_stunting IS NOT NULL";
            
            $result = mysqli_query($this->koneksi, $sql);
            $trainingData = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $converted = $this->convertToCategorical($row);
                if ($converted) {
                    $trainingData[] = $converted;
                }
            }
            
            if (empty($trainingData)) {
                $this->rules = $this->getDefaultRules();
                return $this;
            }
            
            error_log("=== DEBUG C5.0: Data training diambil: " . count($trainingData) . " baris ===");
            
            // Bangun decision tree menggunakan algoritma C5.0
            $this->tree = $this->buildTree($trainingData, $this->attributes);
            
            // Generate rules dari tree
            $this->rules = $this->generateRulesFromTree($this->tree);
            
            // Hitung akurasi
            $this->accuracy = $this->calculateAccuracy($trainingData);
            
            // Simpan ke database
            $this->saveTreeToDatabase($this->tree);
            
            return $this;
            
        } catch (Exception $e) {
            error_log("Training error: " . $e->getMessage());
            $this->rules = $this->getDefaultRules();
            return $this;
        }
    }
    
    // Menghitung entropy
    private function calculateEntropy($data) {
        $total = count($data);
        if ($total == 0) return 0;
        
        $classCounts = [];
        foreach ($data as $row) {
            $class = isset($row[$this->targetAttribute]) ? $row[$this->targetAttribute] : '';
            if (!empty($class)) {
                if (!isset($classCounts[$class])) {
                    $classCounts[$class] = 0;
                }
                $classCounts[$class]++;
            }
        }
        
        $entropy = 0;
        foreach ($classCounts as $count) {
            $probability = $count / $total;
            if ($probability > 0) {
                $entropy -= $probability * log($probability, 2);
            }
        }
        
        return $entropy;
    }
    
    // Menghitung information gain
    private function calculateInformationGain($data, $attribute) {
        $total = count($data);
        if ($total == 0) return 0;
        
        $originalEntropy = $this->calculateEntropy($data);
        
        // Group data berdasarkan nilai atribut
        $groups = [];
        foreach ($data as $row) {
            if (isset($row[$attribute])) {
                $value = $row[$attribute];
                if (!isset($groups[$value])) {
                    $groups[$value] = [];
                }
                $groups[$value][] = $row;
            }
        }
        
        // Hitung weighted entropy
        $weightedEntropy = 0;
        foreach ($groups as $groupData) {
            $groupCount = count($groupData);
            $groupEntropy = $this->calculateEntropy($groupData);
            $weightedEntropy += ($groupCount / $total) * $groupEntropy;
        }
        
        return $originalEntropy - $weightedEntropy;
    }
    
    // Menghitung split info
    private function calculateSplitInfo($data, $attribute) {
        $total = count($data);
        if ($total == 0) return 0;
        
        $groups = [];
        foreach ($data as $row) {
            if (isset($row[$attribute])) {
                $value = $row[$attribute];
                if (!isset($groups[$value])) {
                    $groups[$value] = 0;
                }
                $groups[$value]++;
            }
        }
        
        $splitInfo = 0;
        foreach ($groups as $count) {
            $probability = $count / $total;
            if ($probability > 0) {
                $splitInfo -= $probability * log($probability, 2);
            }
        }
        
        return $splitInfo;
    }
    
    // Menghitung gain ratio
    private function calculateGainRatio($data, $attribute) {
        $informationGain = $this->calculateInformationGain($data, $attribute);
        $splitInfo = $this->calculateSplitInfo($data, $attribute);
        
        if ($splitInfo == 0) return 0;
        return $informationGain / $splitInfo;
    }
    
    // Memilih atribut terbaik berdasarkan gain ratio
    private function chooseBestAttribute($data, $attributes) {
        $bestAttribute = null;
        $bestGainRatio = -1;
        
        foreach ($attributes as $attribute) {
            if ($attribute == $this->targetAttribute) continue;
            
            $gainRatio = $this->calculateGainRatio($data, $attribute);
            
            error_log("Gain Ratio C5.0 untuk $attribute: " . round($gainRatio, 4));
            
            if ($gainRatio > $bestGainRatio) {
                $bestGainRatio = $gainRatio;
                $bestAttribute = $attribute;
            }
        }
        
        error_log("Atribut terbaik C5.0 yang dipilih: $bestAttribute dengan Gain Ratio: $bestGainRatio");
        
        return $bestAttribute;
    }
    
    // Membangun pohon keputusan secara rekursif
    private function buildTree($data, $attributes, $parentNodeId = 'root', $nilaiAtribut = '') {
        // Base case: jika tidak ada data
        if (count($data) == 0) {
            error_log("buildTree C5.0: data kosong");
            return [];
        }
        
        $tree = [];
        
        // Cek distribusi kelas
        $classCounts = [];
        foreach ($data as $row) {
            $class = isset($row[$this->targetAttribute]) ? $row[$this->targetAttribute] : '';
            if (!empty($class)) {
                if (!isset($classCounts[$class])) {
                    $classCounts[$class] = 0;
                }
                $classCounts[$class]++;
            }
        }
        
        $uniqueClasses = array_keys($classCounts);
        
        // Debug informasi node
        error_log("Node C5.0: parent=$parentNodeId, value=$nilaiAtribut, data=" . count($data) . ", classes=" . implode(',', $uniqueClasses));
        
        // Case 1: Semua data memiliki kelas yang sama
        if (count($uniqueClasses) == 1) {
            $tree[] = [
                'node_id' => 'leaf_' . uniqid(),
                'parent_node_id' => $parentNodeId,
                'atribut' => '',
                'nilai_atribut' => $nilaiAtribut,
                'keputusan' => $uniqueClasses[0],
                'jumlah_data' => count($data),
                'class_distribution' => $classCounts,
                'confidence' => 1.0
            ];
            error_log("C5.0 membuat leaf node: " . $uniqueClasses[0]);
            return $tree;
        }
        
        // Case 2: Tidak ada atribut yang tersisa
        if (empty($attributes)) {
            arsort($classCounts);
            $majorityClass = key($classCounts);
            
            $confidence = isset($classCounts[$majorityClass]) ? 
                         $classCounts[$majorityClass] / count($data) : 0.5;
            
            $tree[] = [
                'node_id' => 'leaf_' . uniqid(),
                'parent_node_id' => $parentNodeId,
                'atribut' => '',
                'nilai_atribut' => $nilaiAtribut,
                'keputusan' => $majorityClass,
                'jumlah_data' => count($data),
                'class_distribution' => $classCounts,
                'confidence' => $confidence
            ];
            error_log("C5.0 membuat leaf node (no attributes): " . $majorityClass);
            return $tree;
        }
        
        // Pilih atribut terbaik
        $bestAttribute = $this->chooseBestAttribute($data, $attributes);
        
        // Jika tidak ada atribut yang bagus, buat leaf node
        if ($bestAttribute === null) {
            arsort($classCounts);
            $majorityClass = key($classCounts);
            
            $confidence = isset($classCounts[$majorityClass]) ? 
                         $classCounts[$majorityClass] / count($data) : 0.5;
            
            $tree[] = [
                'node_id' => 'leaf_' . uniqid(),
                'parent_node_id' => $parentNodeId,
                'atribut' => '',
                'nilai_atribut' => $nilaiAtribut,
                'keputusan' => $majorityClass,
                'jumlah_data' => count($data),
                'class_distribution' => $classCounts,
                'confidence' => $confidence
            ];
            error_log("C5.0 membuat leaf node (bestAttribute null): " . $majorityClass);
            return $tree;
        }
        
        // Buat decision node
        $nodeId = 'node_' . uniqid();
        
        // Untuk root node
        if ($parentNodeId === 'root') {
            $tree[] = [
                'node_id' => $nodeId,
                'parent_node_id' => 'root',
                'atribut' => $bestAttribute,
                'nilai_atribut' => '',
                'keputusan' => '',
                'jumlah_data' => count($data),
                'class_distribution' => $classCounts,
                'confidence' => 0
            ];
            error_log("C5.0 membuat ROOT node: $bestAttribute");
        } else {
            $tree[] = [
                'node_id' => $nodeId,
                'parent_node_id' => $parentNodeId,
                'atribut' => $bestAttribute,
                'nilai_atribut' => $nilaiAtribut,
                'keputusan' => '',
                'jumlah_data' => count($data),
                'class_distribution' => $classCounts,
                'confidence' => 0
            ];
            error_log("C5.0 membuat decision node: $bestAttribute = $nilaiAtribut");
        }
        
        // Hapus atribut yang sudah dipilih
        $remainingAttributes = array_diff($attributes, [$bestAttribute]);
        
        // Group data berdasarkan nilai atribut terbaik
        $groups = [];
        foreach ($data as $row) {
            if (isset($row[$bestAttribute])) {
                $value = $row[$bestAttribute];
                if (!isset($groups[$value])) {
                    $groups[$value] = [];
                }
                $groups[$value][] = $row;
            }
        }
        
        // Debug groups
        foreach ($groups as $value => $groupData) {
            error_log("  Grup C5.0 $bestAttribute=$value: " . count($groupData) . " data");
        }
        
        // Rekursi untuk setiap nilai atribut
        foreach ($groups as $value => $groupData) {
            $childTree = $this->buildTree($groupData, $remainingAttributes, $nodeId, $value);
            $tree = array_merge($tree, $childTree);
        }
        
        return $tree;
    }
    
    // Simpan pohon ke database
    private function saveTreeToDatabase($tree) {
        // Hapus data lama
        $deleteResult = mysqli_query($this->koneksi, "DELETE FROM pohon_keputusan_c50");
        if (!$deleteResult) {
            error_log("C5.0 Gagal menghapus data lama: " . mysqli_error($this->koneksi));
            return false;
        }
        
        // Simpan data baru
        $successCount = 0;
        foreach ($tree as $node) {
            $classDistribution = isset($node['class_distribution']) ? 
                json_encode($node['class_distribution']) : '{}';
            
            $confidence = isset($node['confidence']) ? $node['confidence'] : 0;
            
            $query = "INSERT INTO pohon_keputusan_c50 
                     (node_id, parent_node_id, atribut, nilai_atribut, keputusan, 
                      jumlah_data, class_distribution, confidence) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = mysqli_prepare($this->koneksi, $query);
            if (!$stmt) {
                error_log("C5.0 Gagal prepare statement: " . mysqli_error($this->koneksi));
                continue;
            }
            
            mysqli_stmt_bind_param($stmt, "sssssiss", 
                $node['node_id'],
                $node['parent_node_id'],
                $node['atribut'],
                $node['nilai_atribut'],
                $node['keputusan'],
                $node['jumlah_data'],
                $classDistribution,
                $confidence
            );
            
            if (mysqli_stmt_execute($stmt)) {
                $successCount++;
            } else {
                error_log("C5.0 Gagal insert node: " . mysqli_error($this->koneksi));
            }
            
            if ($stmt) {
                mysqli_stmt_close($stmt);
            }
        }
        
        error_log("C5.0 Total node yang disimpan: $successCount dari " . count($tree));
        return $successCount > 0;
    }
    
    // Ambil pohon dari database
    private function getTreeFromDatabase() {
        $query = "SELECT * FROM pohon_keputusan_c50 ORDER BY id";
        $result = mysqli_query($this->koneksi, $query);
        
        if (!$result) {
            error_log("Error C5.0 mengambil tree dari database: " . mysqli_error($this->koneksi));
            return [];
        }
        
        $treeData = [];
        while ($row = mysqli_fetch_assoc($result)) {
            // Decode class_distribution
            if (!empty($row['class_distribution']) && $row['class_distribution'] != '{}') {
                $row['class_distribution'] = json_decode($row['class_distribution'], true);
            } else {
                $row['class_distribution'] = [];
            }
            $treeData[] = $row;
        }
        
        error_log("C5.0 Total node dari database: " . count($treeData));
        return $treeData;
    }
    
    // Prediksi data baru
    public function predict($data) {
        // Ambil pohon dari database
        $treeData = $this->getTreeFromDatabase();
        
        if (empty($treeData)) {
            return [
                'status' => 'Pohon belum dibangun',
                'confidence' => 0,
                'error' => 'Tidak ada pohon keputusan yang tersimpan',
                'rule_used' => 'Model belum dilatih'
            ];
        }
        
        // Konversi data baru
        $categoricalData = $this->convertNewDataToCategorical($data);
        
        // Debug data yang dikonversi
        error_log("Data C5.0 untuk prediksi (setelah konversi):");
        foreach ($categoricalData as $key => $value) {
            error_log("  $key: $value");
        }
        
        // Cari root node
        $rootNode = null;
        foreach ($treeData as $node) {
            if ($node['parent_node_id'] === 'root') {
                $rootNode = $node;
                break;
            }
        }
        
        if (!$rootNode) {
            return [
                'status' => 'Root node tidak ditemukan',
                'confidence' => 0,
                'error' => 'Struktur pohon tidak valid',
                'rule_used' => 'Struktur tidak valid'
            ];
        }
        
        // Lakukan prediksi
        $result = $this->traverseTree($categoricalData, $treeData, $rootNode['node_id']);
        
        if ($result) {
            return [
                'status' => $result['keputusan'],
                'confidence' => $result['confidence'],
                'rule_used' => $this->getRuleDescription($result['path'] ?? [], $result['keputusan']),
                'tree_path' => $result['path'],
                'error' => null
            ];
        } else {
            return [
                'status' => 'Tidak Diketahui',
                'confidence' => 0,
                'error' => 'Tidak dapat melakukan prediksi',
                'rule_used' => 'Rule tidak ditemukan'
            ];
        }
    }
    
    // Traverse tree untuk prediksi
    private function traverseTree($data, $tree, $currentNodeId, $path = []) {
        // Cari node saat ini
        $currentNode = null;
        foreach ($tree as $node) {
            if ($node['node_id'] === $currentNodeId) {
                $currentNode = $node;
                break;
            }
        }
        
        if (!$currentNode) {
            return null;
        }
        
        // Jika ini leaf node, return hasil
        if (!empty($currentNode['keputusan'])) {
            return [
                'keputusan' => $currentNode['keputusan'],
                'confidence' => $currentNode['confidence'],
                'path' => $path
            ];
        }
        
        // Jika decision node, cari child yang cocok
        $attribute = $currentNode['atribut'];
        if (!isset($data[$attribute])) {
            error_log("C5.0 Atribut $attribute tidak ditemukan dalam data input");
            return null;
        }
        
        $attributeValue = $data[$attribute];
        
        // Cari child node yang sesuai
        foreach ($tree as $node) {
            if ($node['parent_node_id'] === $currentNodeId && $node['nilai_atribut'] === $attributeValue) {
                // Tambahkan path
                $newPath = $path;
                $newPath[] = "{$attribute} = {$attributeValue}";
                
                error_log("C5.0 Traverse: $attribute = $attributeValue -> node " . $node['node_id']);
                return $this->traverseTree($data, $tree, $node['node_id'], $newPath);
            }
        }
        
        // Jika tidak ditemukan child yang cocok
        error_log("C5.0 Tidak ditemukan child untuk $attribute = $attributeValue");
        
        // Coba cari child dengan data terbanyak sebagai fallback
        $bestChild = null;
        $maxData = 0;
        
        foreach ($tree as $node) {
            if ($node['parent_node_id'] === $currentNodeId) {
                if ($node['jumlah_data'] > $maxData) {
                    $maxData = $node['jumlah_data'];
                    $bestChild = $node;
                }
            }
        }
        
        if ($bestChild) {
            $newPath = $path;
            $newPath[] = "{$attribute} = {$bestChild['nilai_atribut']} (fallback)";
            
            error_log("C5.0 Fallback ke child: $attribute = " . $bestChild['nilai_atribut']);
            return $this->traverseTree($data, $tree, $bestChild['node_id'], $newPath);
        }
        
        return null;
    }
    
    // Helper untuk membuat deskripsi rule dari path
    private function getRuleDescription($path, $predictedClass) {
        if (empty($path)) {
            return "Default rule: " . $predictedClass;
        }
        
        $conditions = [];
        foreach ($path as $step) {
            // Hilangkan (fallback) dari path
            $cleanStep = str_replace(" (fallback)", "", $step);
            $conditions[] = $cleanStep;
        }
        
        return "IF " . implode(' AND ', $conditions) . " THEN " . $predictedClass;
    }
    
    // Generate rules dari tree
    private function generateRulesFromTree($tree) {
        $rules = [];
        
        // Cari semua leaf nodes
        foreach ($tree as $node) {
            if (!empty($node['keputusan'])) {
                // Cari path ke leaf node ini
                $path = $this->getPathToNode($tree, $node['node_id']);
                
                if (!empty($path)) {
                    $rule = $this->getRuleDescription($path, $node['keputusan']);
                    
                    $rules[] = [
                        'id' => count($rules) + 1,
                        'rule' => $rule,
                        'confidence' => $node['confidence'],
                        'priority' => count($rules) + 1,
                        'output' => $node['keputusan']
                    ];
                }
            }
        }
        
        return $rules;
    }
    
    
    private function getPathToNode($tree, $nodeId, $path = []) {
        // Cari node
        $node = null;
        foreach ($tree as $n) {
            if ($n['node_id'] === $nodeId) {
                $node = $n;
                break;
            }
        }
        
        if (!$node) {
            return $path;
        }
        
        // Jika ini root, kembalikan path
        if ($node['parent_node_id'] === 'root') {
            return $path;
        }
        
        // Cari parent node
        $parentNode = null;
        foreach ($tree as $n) {
            if ($n['node_id'] === $node['parent_node_id']) {
                $parentNode = $n;
                break;
            }
        }
        
        if (!$parentNode) {
            return $path;
        }
        
        // Tambahkan kondisi ke path
        if (!empty($node['nilai_atribut'])) {
            $newPath = ["{$parentNode['atribut']} = {$node['nilai_atribut']}"];
            $newPath = array_merge($newPath, $path);
            
            // Rekursi ke parent
            return $this->getPathToNode($tree, $parentNode['node_id'], $newPath);
        }
        
        return $path;
    }
    
    // Hitung akurasi
    private function calculateAccuracy($data) {
        if (empty($data)) return 0;
        
        $correct = 0;
        $total = 0;
        
        foreach ($data as $row) {
            $prediction = $this->predictFromRow($row);
            if ($prediction['status'] === $row['status_stunting']) {
                $correct++;
            }
            $total++;
        }
        
        return $total > 0 ? ($correct / $total) * 100 : 0;
    }
    
    // Prediksi dari row data
    private function predictFromRow($row) {
        return $this->predict([
            'usia_bulan' => $row['usia_bulan'],
            'jenis_kelamin' => $row['jenis_kelamin'],
            'berat_badan' => $row['berat_badan'],
            'tinggi_badan' => $row['tinggi_badan']
        ]);
    }
    
    // Prediksi massal untuk data testing
    public function predictAllTestingData($saveToDatabase = false) {
        try {
            // Ambil data testing dari database
            $sql = "SELECT 
                        id_training,
                        usia_bulan,
                        jenis_kelamin,
                        berat_badan,
                        tinggi_badan,
                        status_stunting,
                        tipe_data
                    FROM training_stunting 
                    WHERE tipe_data = 'Testing' 
                    AND status_stunting IS NOT NULL";
            
            $result = mysqli_query($this->koneksi, $sql);
            
            if (!$result) {
                throw new Exception("Query error: " . mysqli_error($this->koneksi));
            }
            
            $testingData = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $testingData[] = $row;
            }
            
            if (empty($testingData)) {
                return [
                    'success' => false,
                    'message' => 'Tidak ada data testing yang ditemukan',
                    'results' => [],
                    'summary' => []
                ];
            }
            
            $predictions = [];
            $correct = 0;
            $total = count($testingData);
            
            // Array untuk menyimpan hasil evaluasi
            $evaluation = [
                'total_data' => $total,
                'correct_predictions' => 0,
                'incorrect_predictions' => 0,
                'accuracy' => 0,
                'confusion_matrix' => [
                    'true_stunting' => 0,
                    'true_tidak_stunting' => 0,
                    'false_stunting' => 0,
                    'false_tidak_stunting' => 0
                ],
                'class_distribution' => [
                    'actual' => [],
                    'predicted' => []
                ]
            ];
            
            // Lakukan prediksi untuk setiap data testing
            foreach ($testingData as $index => $data) {
                
                $inputData = [
                    'usia_bulan' => $data['usia_bulan'],
                    'jenis_kelamin' => $data['jenis_kelamin'],
                    'berat_badan' => $data['berat_badan'],
                    'tinggi_badan' => $data['tinggi_badan']
                ];
                
                // Prediksi menggunakan model C5.0
                $prediction = $this->predict($inputData);
                
                // Bandingkan dengan status aktual
                $actualStatus = $data['status_stunting'];
                $predictedStatus = $prediction['status'];
                $isCorrect = ($actualStatus === $predictedStatus);
                
                if ($isCorrect) {
                    $correct++;
                    
                    // Update confusion matrix untuk prediksi benar
                    if ($actualStatus === 'Stunting') {
                        $evaluation['confusion_matrix']['true_stunting']++;
                    } else {
                        $evaluation['confusion_matrix']['true_tidak_stunting']++;
                    }
                } else {
                    // Update confusion matrix untuk prediksi salah
                    if ($actualStatus === 'Stunting') {
                        $evaluation['confusion_matrix']['false_tidak_stunting']++;
                    } else {
                        $evaluation['confusion_matrix']['false_stunting']++;
                    }
                }
                
                // Simpan hasil prediksi
                $predictions[] = [
                    'id_training' => $data['id_training'],
                    'usia_bulan' => $data['usia_bulan'],
                    'jenis_kelamin' => $data['jenis_kelamin'],
                    'berat_badan' => $data['berat_badan'],
                    'tinggi_badan' => $data['tinggi_badan'],
                    'actual_status' => $actualStatus,
                    'predicted_status' => $predictedStatus,
                    'confidence' => $prediction['confidence'],
                    'is_correct' => $isCorrect,
                    'rule_used' => $prediction['rule_used'],
                    'tree_path' => $prediction['tree_path'] ?? []
                ];
                
                // Simpan ke database jika diminta
                if ($saveToDatabase) {
                    $this->saveTestingPrediction($data, $prediction, $isCorrect);
                }
            }
            
            // Hitung akurasi
            $accuracy = $total > 0 ? ($correct / $total) * 100 : 0;
            $evaluation['accuracy'] = $accuracy;
            $evaluation['correct_predictions'] = $correct;
            $evaluation['incorrect_predictions'] = $total - $correct;
            
            // Hitung distribusi class
            $actualStatuses = array_column($predictions, 'actual_status');
            $predictedStatuses = array_column($predictions, 'predicted_status');
            
            $evaluation['class_distribution']['actual'] = array_count_values($actualStatuses);
            $evaluation['class_distribution']['predicted'] = array_count_values($predictedStatuses);
            
            // Hitung precision, recall, dan f1-score
            $tp = $evaluation['confusion_matrix']['true_stunting'];
            $tn = $evaluation['confusion_matrix']['true_tidak_stunting'];
            $fp = $evaluation['confusion_matrix']['false_stunting'];
            $fn = $evaluation['confusion_matrix']['false_tidak_stunting'];
            
            // Precision untuk kelas Stunting
            $precision_stunting = ($tp + $fp) > 0 ? $tp / ($tp + $fp) : 0;
            
            // Recall untuk kelas Stunting
            $recall_stunting = ($tp + $fn) > 0 ? $tp / ($tp + $fn) : 0;
            
            // F1-Score untuk kelas Stunting
            $f1_stunting = ($precision_stunting + $recall_stunting) > 0 
                ? 2 * ($precision_stunting * $recall_stunting) / ($precision_stunting + $recall_stunting) 
                : 0;
            
            // Precision untuk kelas Tidak Stunting
            $precision_tidak = ($tn + $fn) > 0 ? $tn / ($tn + $fn) : 0;
            
            // Recall untuk kelas Tidak Stunting
            $recall_tidak = ($tn + $fp) > 0 ? $tn / ($tn + $fp) : 0;
            
            // F1-Score untuk kelas Tidak Stunting
            $f1_tidak = ($precision_tidak + $recall_tidak) > 0 
                ? 2 * ($precision_tidak * $recall_tidak) / ($precision_tidak + $recall_tidak) 
                : 0;
            
            $evaluation['performance_metrics'] = [
                'precision_stunting' => round($precision_stunting, 4),
                'recall_stunting' => round($recall_stunting, 4),
                'f1_stunting' => round($f1_stunting, 4),
                'precision_tidak' => round($precision_tidak, 4),
                'recall_tidak' => round($recall_tidak, 4),
                'f1_tidak' => round($f1_tidak, 4)
            ];
            
            return [
                'success' => true,
                'message' => "Berhasil memprediksi $total data testing",
                'results' => $predictions,
                'summary' => $evaluation,
                'accuracy_percentage' => round($accuracy, 2)
            ];
            
        } catch (Exception $e) {
            error_log("Error in predictAllTestingData: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'results' => [],
                'summary' => []
            ];
        }
    }
    
    // Simpan prediksi testing ke database
    private function saveTestingPrediction($data, $prediction, $isCorrect) {
        try {
            $id_training = intval($data['id_training']);
            $actual_status = mysqli_real_escape_string($this->koneksi, $data['status_stunting']);
            $predicted_status = mysqli_real_escape_string($this->koneksi, $prediction['status']);
            $confidence = floatval($prediction['confidence']);
            $rule_used = mysqli_real_escape_string($this->koneksi, $prediction['rule_used']);
            $is_correct = $isCorrect ? 1 : 0;
            
            // Cek apakah sudah ada prediksi untuk data ini
            $checkSql = "SELECT id_prediksi FROM prediksi_testing WHERE id_training = $id_training";
            $checkResult = mysqli_query($this->koneksi, $checkSql);
            
            if ($checkResult && mysqli_num_rows($checkResult) > 0) {
                // Update jika sudah ada
                $updateSql = "UPDATE prediksi_testing SET 
                             predicted_status = '$predicted_status',
                             confidence = $confidence,
                             is_correct = $is_correct,
                             rule_used = '$rule_used',
                             tanggal_prediksi = NOW()
                             WHERE id_training = $id_training";
                
                mysqli_query($this->koneksi, $updateSql);
            } else {
                $insertSql = "INSERT INTO prediksi_testing 
                             (id_training, actual_status, predicted_status, confidence, 
                              is_correct, rule_used, tanggal_prediksi) 
                             VALUES ($id_training, '$actual_status', '$predicted_status', 
                                     $confidence, $is_correct, '$rule_used', NOW())";
                
                mysqli_query($this->koneksi, $insertSql);
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log("Error saving testing prediction: " . $e->getMessage());
            return false;
        }
    }
    
    // Tampilkan struktur pohon
    public function displayTree() {
        $treeData = $this->getTreeFromDatabase();
        
        if (empty($treeData)) {
            echo "<div class='alert alert-warning'>Pohon C5.0 belum dibangun</div>";
            return;
        }
        
        echo "<div class='card mb-3'>
                <div class='card-header'>
                    <h5>Struktur Pohon Keputusan C5.0</h5>
                </div>
                <div class='card-body'>
                    <pre style='max-height: 500px; overflow-y: auto;'>";
        
        $this->printTreeStructure($treeData, 'root', 0);
        
        echo "</pre></div></div>";
    }
    
    // Cetak struktur tree
    private function printTreeStructure($tree, $parentId, $level) {
        foreach ($tree as $node) {
            if ($node['parent_node_id'] == $parentId) {
                $indent = str_repeat("  ", $level);
                
                // Cari parent node untuk mendapatkan atribut dan nilainya
                $parentNode = null;
                if ($node['parent_node_id'] !== 'root') {
                    foreach ($tree as $parent) {
                        if ($parent['node_id'] === $node['parent_node_id']) {
                            $parentNode = $parent;
                            break;
                        }
                    }
                }
                
                if ($node['atribut']) {
                    echo $indent . "🔍 Atribut: " . $node['atribut'] . "\n";
                    
                    // Tampilkan kondisi dari parent dengan benar
                    if ($parentNode && $node['nilai_atribut'] !== '') {
                        echo $indent . "  ↳ Jika " . $parentNode['atribut'] . " = " . $node['nilai_atribut'] . "\n";
                    }
                    
                    echo $indent . "  (Data: " . $node['jumlah_data'] . ")\n";
                    
                    // Cetak distribusi kelas
                    if (!empty($node['class_distribution']) && is_array($node['class_distribution'])) {
                        $distStr = "";
                        foreach ($node['class_distribution'] as $class => $count) {
                            $distStr .= "$class: $count, ";
                        }
                        echo $indent . "  Distribusi: " . rtrim($distStr, ", ") . "\n";
                    }
                    
                    $this->printTreeStructure($tree, $node['node_id'], $level + 1);
                } else if ($node['keputusan']) {
                    echo $indent . "📊 Keputusan: " . $node['keputusan'];
                    
                    // Tampilkan kondisi dari parent untuk leaf nodes
                    if ($parentNode && $node['nilai_atribut'] !== '') {
                        echo " (Jika " . $parentNode['atribut'] . " = " . $node['nilai_atribut'] . ")";
                    }
                    
                    echo " (Confidence: " . round($node['confidence'] * 100, 1) . "%, Data: " . $node['jumlah_data'] . ")\n";
                }
            }
        }
    }
    
    // Default rules untuk fallback
    private function getDefaultRules() {
        return [            
            [
                'id' => 1,
                'rule' => 'IF usia_bulan = 11-24 AND tinggi_badan = 70-83 THEN Stunting',
                'confidence' => 0.85,
                'priority' => 1,
                'output' => 'Stunting'
            ],
            [
                'id' => 2,
                'rule' => 'IF usia_bulan = 25-36 AND tinggi_badan = 83-96 THEN Stunting',
                'confidence' => 0.80,
                'priority' => 2,
                'output' => 'Stunting'
            ],
            [
                'id' => 3,
                'rule' => 'IF berat_badan = 7.5-11.5 AND usia_bulan = 11-24 THEN Stunting',
                'confidence' => 0.75,
                'priority' => 3,
                'output' => 'Stunting'
            ],
            [
                'id' => 4,
                'rule' => 'IF tinggi_badan >= standar_minimal THEN Tidak Stunting',
                'confidence' => 0.90,
                'priority' => 4,
                'output' => 'Tidak Stunting'
            ],
            [
                'id' => 5,
                'rule' => 'ELSE Tidak Stunting',
                'confidence' => 0.60,
                'priority' => 5,
                'output' => 'Tidak Stunting'
            ]
        ];
    }
    
    // Simpan prediksi individual
    public function savePrediction($data, $prediction, $balitaId = null, $id_pengukuran = null) {
        try {
            $balitaId = $balitaId ? intval($balitaId) : 'NULL';
            $id_pengukuran = $id_pengukuran ? intval($id_pengukuran) : 'NULL';
            $modelId = 2;
            $usia = intval($data['usia_bulan'] ?? $data['usia'] ?? 0);
            $berat = floatval($data['berat_badan'] ?? $data['berat'] ?? 0);
            $tinggi = floatval($data['tinggi_badan'] ?? $data['tinggi'] ?? 0);
            $prediksi_status = mysqli_real_escape_string($this->koneksi, $prediction['status']);
            $confidence = floatval($prediction['confidence']);
            
            $sql = "INSERT INTO prediksi_stunting 
                    (id_balita, id_pengukuran, id_model, usia_bulan, berat_badan, tinggi_badan, 
                     prediksi, confidence, benar_salah, tanggal_prediksi) 
                    VALUES ($balitaId, $id_pengukuran, $modelId, $usia, $berat, $tinggi, 
                            '$prediksi_status', $confidence, 'Belum Dicek', NOW())";
            
            if (mysqli_query($this->koneksi, $sql)) {
                return [
                    'success' => true,
                    'id_prediksi' => mysqli_insert_id($this->koneksi),
                    'message' => 'Prediksi berhasil disimpan'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'SQL Error: ' . mysqli_error($this->koneksi)
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
    
    // Dapatkan rekomendasi berdasarkan status
    public function getRecommendation($status, $usia = null, $tinggi = null) {
        if ($status == 'Stunting') {
            $recommendation = "<strong>REKOMENDASI UNTUK STUNTING:</strong><br>";
            $recommendation .= "1. <strong>Konsultasi Medis:</strong> Segera periksa ke dokter anak<br>";
            $recommendation .= "2. <strong>Perbaikan Gizi:</strong> Tingkatkan asupan protein<br>";
            $recommendation .= "3. <strong>Monitoring:</strong> Ukur berat dan tinggi setiap bulan<br>";
            $recommendation .= "4. <strong>Kebersihan:</strong> Jaga kebersihan makanan dan lingkungan<br>";
            
            if ($usia && $usia < 24) {
                $recommendation .= "5. <strong>ASI Eksklusif:</strong> Lanjutkan ASI sampai usia 2 tahun";
            }
            
            return $recommendation;
        } else {
            return "<strong>REKOMENDASI UNTUK TIDAK STUNTING:</strong><br>
                   1. <strong>Pertahankan Pola Makan:</strong> Teruskan makanan bergizi<br>
                   2. <strong>Monitor Rutin:</strong> Tetap kontrol di posyandu<br>
                   3. <strong>Aktivitas Fisik:</strong> Ajak bermain dan bergerak aktif<br>
                   4. <strong>Istirahat Cukup:</strong> Pastikan tidur cukup<br>
                   5. <strong>Imunisasi:</strong> Lengkapi imunisasi";
        }
    }
}
?>