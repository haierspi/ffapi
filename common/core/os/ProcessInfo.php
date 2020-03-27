<?php
namespace ff\os;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 *  https://gist.github.com/bgrimes/4589453
 *  use Symfony\Process http://www.symfonychina.com/doc/current/components/process.html
 *  by haierspi
 */

class ProcessInfo
{

    /** @var array $processes */
    protected $processes = array();

    /**
     * Find a processes
     *
     * @param      $pid
     * @param null $default
     *
     * @return null
     */
    public function findProcessByPid($pid, $default = null)
    {
        // Get the most current list of processes
        $this->getCurrentProcesses();

        // If the process exists
        if (isset($this->processes[$pid])) {
            return $this->processes[$pid];
        } else {
            return $default;
        }
    }

    /**
     * @param bool $current_user_only
     *
     * @return array
     * @throws \Exception
     */
    public function getCurrentProcesses($current_user_only = true)
    {
        // Run `ps aux` or `ps ux` to get the list of running processes
        /*
         * -a      Display information about other users' processes as well as your own.  This will skip any pro-
        cesses which do not have a controlling terminal, unless the -x option is also specified.
         * -u      Display the processes belonging to the specified usernames.
         * -x      When displaying processes matched by other options, include processes which do not have a con-
        trolling terminal.  This is the opposite of the -X option.  If both -X and -x are specified in
        the same command, then ps will use the one which was specified last.
         */
        // Will set the option to ux is only needing processes for the current user
        $command = array("ps", ($current_user_only ? "ux" : "aux"));

        $process = new Process($command);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \Exception("The 'ps' process was not successful: " . $process->getErrorOutput());
        }
        // Get the process output
        $output = $process->getOutput();
        // Convert the output into an arrow of rows
        $output = explode("\n", $output);

        // Get the leading row which happens to be the header row
        $header = array_shift($output);
        // Split the header row on spaces
        $header = preg_split('/ +/', $header);
        // Get the number of columns from the header
        $header_count = count($header);

        // Initialize an array to hold all of the processes
        $this->processes = array();

        foreach ($output as $process) {
            if (empty($process)) {
                continue;
            }
            $process = preg_split('/ +/', $process);

            // The first few columns will match the header, but the last column (COMMAND) could be in multiple
            // array fields because processes are broken up by spaces. So grab the known headers first and then
            // add any remaining array fields from process to the COMMAND field
            $tmp_process = array();
            foreach ($header as $header_name) {
                // Cut the column out of the process
                $column = array_splice($process, 0, 1);
                $tmp_process[$header_name] = $column[0];
            }
            if (!empty($process)) {
                // Add the remaining process fields to the COMMAND field in the process definition
                $tmp_process[$header[$header_count - 1]] .= " " . implode(" ", $process);
            }
            $this->processes[$tmp_process["PID"]] = $tmp_process;
        }

        return $this->processes;
    }

    /**
     * @param bool $current_user_only
     *
     * @return array
     * @throws \Exception
     */

    public function getCurrentProcessesByCommand($Command, $excludeSelf = true, $current_user_only = false)
    {
        // Run `ps aux` or `ps ux` to get the list of running processes
        /*
         * -a      Display information about other users' processes as well as your own.  This will skip any pro-
        cesses which do not have a controlling terminal, unless the -x option is also specified.
         * -u      Display the processes belonging to the specified usernames.
         * -x      When displaying processes matched by other options, include processes which do not have a con-
        trolling terminal.  This is the opposite of the -X option.  If both -X and -x are specified in
        the same command, then ps will use the one which was specified last.
         */
        // Will set the option to ux is only needing processes for the current user
        $command = "ps".($current_user_only ? " ux" : " aux") .'|grep -E "USER|' . $Command . '" |grep -v grep';

        $process = new Process($command);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \Exception("The 'ps' process was not successful: " . $process->getErrorOutput());
        }

        // Get the process output
        $output = $process->getOutput();

        // ddl($output);

        // Convert the output into an arrow of rows
        $output = explode("\n", $output);

        // Get the leading row which happens to be the header row
        $header = array_shift($output);
        // Split the header row on spaces
        $header = preg_split('/ +/', $header);
        // Get the number of columns from the header
        $header_count = count($header);

        // Initialize an array to hold all of the processes
        $this->processes = array();

        foreach ($output as $process) {
            if (empty($process)) {
                continue;
            }
            $process = preg_split('/ +/', $process);

            // The first few columns will match the header, but the last column (COMMAND) could be in multiple
            // array fields because processes are broken up by spaces. So grab the known headers first and then
            // add any remaining array fields from process to the COMMAND field
            $tmp_process = array();
            foreach ($header as $header_name) {
                // Cut the column out of the process
                $column = array_splice($process, 0, 1);
                $tmp_process[$header_name] = $column[0];
            }
            if (!empty($process)) {
                // Add the remaining process fields to the COMMAND field in the process definition
                $tmp_process[$header[$header_count - 1]] .= " " . implode(" ", $process);
            }
            $this->processes[$tmp_process["PID"]] = $tmp_process;
        }

        if ($excludeSelf) {
            $currentExecutePid = posix_getpid();
            unset($this->processes[$currentExecutePid]);
        }

        return $this->processes;
    }

}
