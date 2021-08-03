<?php
// constants
define('TEMPLATE_DIR', __DIR__ . '/templates');
define('DEFAULT_SUFFIX', 'template');
define('KEY_DELIM', '%%');
define('SUFFIX',    '--suffix');
define('NO_PROMPT', '--no-prompt');
define('PWD_KEYS',  '--pwd-keys');
define('OUTPUT_BAR', str_repeat('*', 40) . "\n");

// init vars
$usage = 'Usage: php generate_creds.php <CREDS_JSON_FILE> <TEMPLATES_DIR> [' . SUFFIX . '="template"] [' . PWD_KEYS . '="xxx,yyy"] [' . NO_PROMPT . ']' . "\n"
       . '       ' . SUFFIX . ' is the file extension used for template files' . "\n"
       . '       ' . PWD_KEYS . ' is a comma separated list of "creds.json" file keys that need a random key assigned' . "\n"
       . '       if the ' . NO_PROMPT . '" flag is present, will operate automatically without confirming values' . "\n"
       . '       NOTES: (1) template suffix defaults to "template"' . "\n"
       . '              (2) keys inside template files must be ' 
       . KEY_DELIM . 'wrapped' . KEY_DELIM . ' with this delimiter:"' . KEY_DELIM . '"' . "\n";
$creds = [];	// will write out to security_creds.json

function getValueFromArgv($search)
{
	global $argv;
	$flag = FALSE;
	$value = NULL;
	foreach ($argv as $item) {
		if (strpos($item, $search) !== FALSE) {
			[$flag, $value] = explode('=', $item);
			break;
		}
	}
	if ($flag) {
		$value = trim($value, '\'" ');
	}
	return $value;
}

// access CLI params
$allArgs  = implode(' ', $argv);
$credFile = $argv[1] ?? '';
$templDir = $argv[2] ?? '';
$files    = [];
$keyDelim = KEY_DELIM;
$pwdKeys  = getValueFromArgv(PWD_KEYS) ?? '';
$suffix   = getValueFromArgv(SUFFIX) ?? DEFAULT_SUFFIX;
$noPrompt = (bool) strpos($allArgs, NO_PROMPT);

// check for argv errors
if (!$credFile) {
	echo "You need to provide a security credentials JSON file\n";
	exit($usage);
} elseif (!file_exists($templDir)) {
	echo "Unable to find template files\n";
	exit($usage);
}

// read security creds JSON file contents
try {
	$creds = json_decode(file_get_contents($credFile), TRUE, 512, JSON_THROW_ON_ERROR);
} catch (Throwable $t) {
	echo get_class($t) . ':' . $t->getMessage() . "\n";
	exit($usage);
}

// extracting template contents
foreach ($creds as $fn => $items) {
	$template = str_replace(['//','..'], ['/','.'], $templDir . '/' . $fn . '.' . $suffix);
	if (!file_exists($template)) {
		echo "Unable to find $template\n";
		exit($usage);
	} else {
		echo "Reading $template ...\n";
		$files[$fn] = file_get_contents($template);
	}
}

// generate new passwords if that key is present
if ($pwdKeys) {
	$list = explode(',', $pwdKeys);
	$pwdKeys = [];
	foreach ($list as $key) {
		$pwdKeys[$key] = bin2hex(random_bytes(8));
	}
}

// confirm cred info
if (!$noPrompt) {
	foreach ($creds as $fn => $items) {
		echo OUTPUT_BAR;
		echo "Confirming Creds for $fn\n";
		echo OUTPUT_BAR;
		foreach ($items as $key => $value) {
			$prompt = $key . ' [' . $value . "] : \n";
			$temp = readline($prompt);
			if ($temp) $creds[$fn][$key] = $temp;
		}
	}
}

// write out changes to other files
reset($creds);
foreach ($creds as $fn => $items) {
	echo OUTPUT_BAR;
	echo "Processing $fn ...\n";
	echo OUTPUT_BAR;
	$contents = $files[$fn];
	foreach($items as $key => $value) {
		// generate password if requested
		if (isset($pwdKeys[$key])) {
			$value = $pwdKeys[$key];
			$creds[$fn][$key] = $value;
		}
		$contents = str_replace(KEY_DELIM . $key . KEY_DELIM, $value, $contents);
	}
	file_put_contents($fn, $contents);
	readfile($fn);
	echo "\n";
}

// write out changes to security creds file
$backFn = str_replace('.','_',$credFile) . '_' . date('YmdHis') . '.bak';
copy($credFile, $backFn);
file_put_contents($credFile, json_encode($creds, JSON_PRETTY_PRINT));
echo OUTPUT_BAR;
echo "Security Credentials File:\n";
echo OUTPUT_BAR;
readfile($credFile);
