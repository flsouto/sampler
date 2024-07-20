from pydub import AudioSegment
import sys

sound1 = AudioSegment.from_file(sys.argv[1])
sound1 = sound1.fade(from_gain=int(sys.argv[2]), to_gain=int(sys.argv[3]), start=0, duration=len(sound1))
sound1.export(sys.argv[4],format="wav")
