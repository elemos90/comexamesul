import os

filepath = 'app/Views/juries/planning.php'

try:
    with open(filepath, 'r', encoding='utf-8') as f:
        lines = f.readlines()

    start_idx = -1
    end_idx = -1

    # Find the block related to allLocationJuries (the flattened loop)
    # We want to remove the loop AND the Subtotal row following it.
    # The Subtotal row ends around "// Fim flattened block"
    
    for i, line in enumerate(lines):
        if 'foreach ($allLocationJuries as $jury):' in line:
            # We want the FIRST occurrence after line 1100 approx?
            # Or assume there is only one left (since I removed the definition)
            # Actually, I added a loop above BUT that loop iterates supervisordGroups.
            # So this exact string should only appear in the legacy block.
            start_idx = i
            break # Found start
            
    if start_idx != -1:
        # Find end from start
        for i in range(start_idx, len(lines)):
            if '// Fim flattened block' in line: # This comment might be missing if I messed up view?
                # Check view content again. Line 1394 had "<?php // Fim flattened block ?>"
                pass
            
            if 'background: linear-gradient(90deg' in lines[i] and 'subtotal-row' in lines[i]:
                 # This distinguishes subtotal rows. We want to skip the subtotal row of "allLocationJuries".
                 pass

            if '// Fim flattened block' in lines[i]:
                end_idx = i
                break

    if start_idx != -1 and end_idx != -1 and end_idx > start_idx:
        print(f"Removing lines {start_idx+1} to {end_idx+1}")
        # Remove lines inclusive
        new_lines = lines[:start_idx] + lines[end_idx+1:]
        
        with open(filepath, 'w', encoding='utf-8') as f:
            f.writelines(new_lines)
        print("Success: File updated.")
    else:
        print(f"Error: Markers not found correctly. Start: {start_idx}, End: {end_idx}")
        # Debug print around likely area
        if start_idx != -1:
             print("Found start. Looking for end...")

except Exception as e:
    print(f"Exception: {e}")
