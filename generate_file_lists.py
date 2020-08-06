import os

def write_path(folder, prefix, fd):
    for i in os.listdir(folder):
        prefix_path = os.path.join(prefix, i)
        base_path = os.path.join(folder, i)
        if os.path.isdir(base_path):
            if i not in ['rest', 'function']:
                write_path(base_path, prefix_path, fd)
        else:
            fd.write(prefix_path+"\n")

fd = open("file_lists.txt", "w")
write_path("src", "", fd)
        
