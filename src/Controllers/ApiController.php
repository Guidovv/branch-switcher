<?php

namespace BranchSwitcher\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ApiController extends BaseController
{
    private $inPublicFolder;
    private $error = false;

    /**
     * Switch the branch
     *
     * @return Object
     */
    public function switch(Request $request): Object
    {
        $response = [
            'error' => false,
            'success' => false,
            'switched' => false,
            'message' => '',
            'output' => []
        ];

        // Retrieve all branches
        $branches = getBranches();

        $directories = explode('/', getcwd());
        $this->inPublicFolder = end($directories) == 'public';

        if ($request->input('branch') == $branches['current']) {
            // Staying on the current branch
            $response['message'] = 'Staying on current branch';
        }
        elseif (in_array($request->input('branch'), $branches['all'])) {
            // Switching branch
            $checkoutOutput = $response['output']['git checkout'] = shell_exec('git checkout ' . $request->input('branch') . ' 2>&1');

            if (! Str::contains(strtolower($checkoutOutput), 'switched to branch')) {
                $response['message'] = 'Couldn\'t switch to the selected branch';
                $response['error'] = true;

                return response()->json($response);
            }

            $response['message'] = $checkoutOutput;
            $response['switched'] = true;
        }
        else {
            // Most likely a branch that doesn't exists
            $response['message'] = 'Can\'t switch to this branch';
            $response['error'] = true;

            return response()->json($response);
        }

        $commandsOutput = $this->runSelectedCommands((array) $request->input('commands'));
        $response['output'] = array_merge($response['output'], $commandsOutput);

        $ownCommand = $request->input('own_command');
        if (! empty($ownCommand)) {
            $response['output'][$ownCommand] = $this->runCommand($ownCommand);
        }

        $response['error'] = $this->error;

        return response()->json($response);
    }

    /**
     * Executes the selected commands and returns an
     * array containing the ouput from each command
     *
     * @param  Array $commands
     * @return Array
     */
    private function runSelectedCommands(array $commands = []): array
    {
        $output = [];

        if (empty($commands)) {
            return $output;
        }

        $enabledCommands = app('config')->get('branch-switcher.commands');
        foreach ($commands as $command) {
            if (! isset($enabledCommands[$command])) {
                continue;
            }

            $output[$command] = $this->runCommand($command);
        }

        return $output;
    }

    /**
     * Execute a given command and returns the output
     *
     * @param String $command
     * @return Mixed
     */
    private function runCommand(string $command)
    {
        if ($this->inPublicFolder) {
            $command = 'cd ../;' . $command;
        }

        exec($command . ' 2>&1', $output, $code);

        $output = array_map('trim', $output);
        $output = array_filter($output);
        $output = implode("\n", $output);

        if ($code != 1) {
            $this->error = true;
        }

        return $output;
    }
}
